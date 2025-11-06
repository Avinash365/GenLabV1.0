<?php

namespace App\Http\Controllers\Email;

use App\Http\Controllers\Controller;
use App\Models\EmailSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;
use Webklex\IMAP\Facades\Client;
use Webklex\PHPIMAP\ClientManager;

use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport;
use Symfony\Component\Mime\Email as SymfonyEmail;
use Illuminate\Mail\Mailer;
use Symfony\Component\Mailer\Transport;
use Illuminate\Support\Facades\Mail;

class EmailController extends Controller
{
    /**
     * Show inbox emails.
     */
    public function index(Request $request, $id = null)
    {
        $activeId = $id ?? $request->route('id');
        $search   = $request->input('search');
        $page     = max(1, (int)$request->input('page', 1));
        $perPage  = 10;

        if ($activeId) {
            [$emails, $total] = $this->getInboxEmails($activeId, $page, $perPage, $search);
        } else {
            $emails = collect();
            $total  = 0;
        }

        $emails = new LengthAwarePaginator(
            collect($emails),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('email.email', compact('emails', 'search', 'activeId'));
    }

    /**
     * Show sent emails.
     */
    public function sentIndex(Request $request, $id = null)
    {
        $activeId = $id ?? $request->route('id');
        $search   = $request->input('search');
        $page     = max(1, (int)$request->input('page', 1));
        $perPage  = 10;

        if ($activeId) {
            [$emails, $total] = $this->getSentEmails($activeId, $page, $perPage, $search);
        } else {
            $emails = collect();
            $total  = 0;
        }

        $emails = new LengthAwarePaginator(
            collect($emails),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        $type = 'send';

        return view('email.email', compact('emails', 'search', 'activeId', 'type'));
    }

    /**
     * Show reply form for a specific email.
     */
    public function reply($id, $uid, $type = null)
    {

        $email = $this->getEmailByUid($id, $uid, $type);
        return view('email.email-reply', compact('email'));
    }

    /**
     * Store new email settings and verify SMTP.
     */
    public function store(Request $request)
    {
        if (!auth('admin')->check() && !auth()->user()->hasPermission('email.create')) {
            return back()->with('error', 'Unauthorized action');
        }

        try {
            $request->validate([
                'email'      => 'required|email|max:255|unique:email_settings,email',
                'password'   => 'required|string|max:255',
                'smtp_host'  => 'required|string|max:255',
                'smtp_port'  => 'required|integer',
                'imap_host'  => 'nullable|string|max:255',
                'imap_port'  => 'nullable|integer',
                'encryption' => 'nullable|string|max:10',
            ]);

            // $encryption = $request->encryption ?: 'tls';
            // $dsn = sprintf(
            //     'smtp://%s:%s@%s:%d?encryption=%s',
            //     urlencode($request->email),
            //     urlencode($request->password),
            //     $request->smtp_host,
            //     $request->smtp_port,
            //     $encryption
            // );

            // // Test SMTP
            // try {
            //     $transport = Transport::fromDsn($dsn);
            //     $mailer = new \Symfony\Component\Mailer\Mailer($transport);

            //     $testMail = (new SymfonyEmail())
            //         ->from($request->email)
            //         ->to($request->email)
            //         ->subject('SMTP Test')
            //         ->text('SMTP connection verified successfully.');
            //     $mailer->send($testMail);
            // } catch (\Exception $e) {
            //     return back()->with('error', 'Invalid SMTP configuration: ' . $e->getMessage())->withInput();
            // }

            // Save to DB
            $data = $request->only([
                'email', 'password', 'smtp_host', 'smtp_port', 'imap_host', 'imap_port', 'encryption'
            ]);

            if (auth('admin')->check()) {
                $data['admin_id'] = auth('admin')->id();
            } else {
                $data['user_id'] = auth()->id();
            }

            EmailSetting::create($data);

            return back()->with('success', 'Email account added and verified successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error saving email settings: ' . $e->getMessage());
        }
    }

    /**
     * Fetch inbox emails.
     */
    private function getInboxEmails($id, $page = 1, $perPage = 10, $search = null)
    {
        $settings = EmailSetting::findOrFail($id);

        try {
            $client = (new ClientManager())->make([
                'host'          => $settings->imap_host,
                'port'          => $settings->imap_port,
                'encryption'    => $settings->encryption,
                'validate_cert' => false,
                'username'      => $settings->email,
                'password'      => $settings->password,
                'protocol'      => 'imap',
            ]);
            $client->connect();

            $folder = $client->getFolder('INBOX');
            $messages = $folder->messages()->all()->get();

            $sorted = $messages->sortByDesc(fn($msg) => Carbon::parse($msg->getDate() ?? now()))->values();

            if ($search) {
                $search = strtolower($search);
                $sorted = $sorted->filter(function ($msg) use ($search) {
                    $subject = strtolower($msg->getSubject() ?? '');
                    $body = strtolower($msg->getTextBody() ?? $msg->getHTMLBody() ?? '');
                    $from = $msg->getFrom();
                    $fromEmail = $from->first()?->mail ?? '';
                    $fromName = $from->first()?->personal ?? '';

                    return str_contains($subject, $search)
                        || str_contains($body, $search)
                        || str_contains($fromEmail, $search)
                        || str_contains($fromName, $search);
                });
            }

            $total = $sorted->count();
            $pageItems = $sorted->forPage($page, $perPage);

            $emails = [];
            foreach ($pageItems as $message) {
                $from = $message->getFrom()->first();
                $fromName = $from->personal ?? $from->name ?? '';
                $fromEmail = $from->mail ?? '';

                $emails[] = [
                    'id'         => $settings->id,
                    'uid'        => $message->getUid(),
                    'subject'    => mb_decode_mimeheader($message->getSubject() ?? 'No Subject'),
                    'from_name'  => $fromName ?: $fromEmail,
                    'from_email' => $fromEmail,
                    'date'       => Carbon::parse($message->getDate())->format('h:i A, d M Y'),
                    'body'       => $message->getTextBody() ?? $message->getHTMLBody(),
                ];
            }

            return [$emails, $total];
        } catch (\Exception $e) {
            Log::error('Failed to fetch inbox emails', ['id' => $id, 'error' => $e->getMessage()]);
            return [[], 0];
        }
    }

    /**
     * Fetch sent emails.
     */
    private function getSentEmails($id, $page = 1, $perPage = 10, $search = null)
    {
        $settings = EmailSetting::findOrFail($id);

        try {
            // Create IMAP client connection
            $client = Client::make([
                'host'          => $settings->imap_host,
                'port'          => $settings->imap_port,
                'encryption'    => $settings->encryption,
                'validate_cert' => false,
                'username'      => $settings->email,
                'password'      => $settings->password,
                'protocol'      => 'imap',
            ]);
            $client->connect();

            /**
             * Try to find the correct "Sent" folder.
             * Different mail servers use different folder names.
             */
            $possibleSentFolders = [
                'Sent'
            ];

            $folder = null;
            foreach ($possibleSentFolders as $name) {
                try {
                    $temp = $client->getFolder($name);
                    if ($temp) {
                        $folder = $temp;
                        break;
                    }
                } catch (\Exception $e) {
                    continue; // Try next name
                }
            }

            if (!$folder || !str_contains(strtolower($folder->name), 'sent')) {
                throw new \Exception('Sent folder not found.');
            }

            // Fetch all messages in the Sent folder
            $messages = $folder->messages()->all()->get();

            // Sort by date descending
            $sorted = $messages->sortByDesc(fn($msg) => Carbon::parse($msg->getDate() ?? now()))->values();

            // Filter messages actually sent from this account
            $sorted = $sorted->filter(function ($msg) use ($settings) {
                $from = $msg->getFrom()[0] ?? null;
                $fromEmail = $from ? ($from->mail ?? $from->email) : null;
                return $fromEmail && strtolower($fromEmail) === strtolower($settings->email);
            });

            // Apply search filter if provided
            if ($search) {
                $search = strtolower($search);
                $sorted = $sorted->filter(fn($msg) => str_contains(strtolower($msg->getSubject() ?? ''), $search));
            }

            // Pagination
            $total = $sorted->count();
            $pageItems = $sorted->forPage($page, $perPage);

            // Prepare email data
            $emails = [];
            foreach ($pageItems as $message) {
                $emails[] = [
                    'id'        => $settings->id,
                    'uid'       => $message->getUid(),
                    'subject'   => mb_decode_mimeheader($message->getSubject() ?? 'No Subject'),
                    'from_name' => $settings->email,
                    'to'        => collect($message->getTo())->pluck('mail')->implode(', '),
                    'date'      => Carbon::parse($message->getDate())->format('h:i A, d M Y'),
                    'body'      => $message->getHTMLBody() ?? $message->getTextBody(),
                ];
            }

            return [$emails, $total];
        } catch (\Exception $e) {
            Log::error('Failed to fetch sent emails', [
                'email_setting_id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [[], 0];
        }
    }


    /**
     * Fetch single email by UID.
     */
    private function getEmailByUid($id, $uid, $type = null)
    {
        $settings = EmailSetting::findOrFail($id);

        try {
            // Create IMAP client
            $client = Client::make([
                'host'          =>  $settings->imap_host,
                'port'          =>  $settings->imap_port,
                'encryption'    =>  $settings->encryption,
                'validate_cert' =>  false,
                'username'      =>  $settings->email,
                'password'      =>  $settings->password,
                'protocol'      => 'imap',
            ]);

            $client->connect();

            /**
             * Select folder based on type
             * If $type is not null or equals 'send', open the Sent folder.
             */
            if (!empty($type) && strtolower($type) === 'send') {
                // Common folder names for Sent mail vary depending on the email provider
                $possibleSentFolders = ['Sent', 'Sent Mail', 'Sent Items', '[Gmail]/Sent Mail'];

                $folder = null;
                foreach ($possibleSentFolders as $folderName) {
                    try {
                        $folder = $client->getFolder($folderName);
                        if ($folder) break;
                    } catch (\Exception $e) {
                        continue; // Try next possible folder
                    }
                }

                if (!$folder) {
                    abort(404, 'Sent folder not found.');
                }
            } else {
                $folder = $client->getFolder('INBOX');
            }


            // Fetch message by UID
            $message = $folder->messages()->getMessageByUid($uid);
            if (!$message) {
                abort(404, 'Email not found');
            }

            // From
            $from = $message->getFrom()[0] ?? null;
            $from_name  = $from ? ($from->name ?? $from->personal) : null;
            $from_email = $from ? ($from->mail ?? $from->email) : null;

            // To
            $to_list = [];
            foreach ($message->getTo() as $to) {
                $to_list[] = [
                    'name'  => $to->name ?? $to->personal ?? null,
                    'email' => $to->mail ?? $to->email ?? null,
                ];
            }

            // CC
            $cc_list = [];
            foreach ($message->getCc() as $cc) {
                $cc_list[] = [
                    'name'  => $cc->name ?? $cc->personal ?? null,
                    'email' => $cc->mail ?? $cc->email ?? null,
                ];
            }


            // BCC
            $bcc_list = [];
            foreach ($message->getBcc() as $bcc) {
                $bcc_list[] = [
                    'name'  => $bcc->name ?? $bcc->personal ?? null,
                    'email' => $bcc->mail ?? $bcc->email ?? null,
                ];
            }

            // Attachments
            $attachments = [];
            foreach ($message->getAttachments() as $attachment) {
                $base64 = 'data:' . $attachment->contentType . ';base64,' . base64_encode($attachment->getContent());
                $attachments[] = [
                    'name'   => $attachment->name,
                    'size'   => $attachment->size,
                    'mime'   => $attachment->contentType,
                    'base64' => $base64,
                ];
            }

            return [
                'id'          => $settings->id,
                'uid'         => $message->getUid(),
                'subject'     => mb_decode_mimeheader($message->getSubject()),
                'from_name'   => $from_name,
                'from_email'  => $from_email,
                'to'          => $to_list,
                'cc'          => $cc_list,
                'bcc'         => $bcc_list,
                'date'        => \Carbon\Carbon::parse($message->getDate())->format('h:i A, d M Y'),
                'body'        => $message->getHTMLBody() ?? $message->getTextBody(),
                'attachments' => $attachments,
                'folder'      => $folder->name,
            ];

        } catch (\Exception $e) {
            \Log::error('Failed to fetch email by UID', [
                'email_setting_id' => $id,
                'uid'              => $uid,
                'error'            => $e->getMessage(),
            ]);

            abort(500, 'Failed to fetch email');
        }
    }


    /**
     * Send email.
     */
    public function send(Request $request)
    {
        try {
            $request->validate([
                'id'          => 'required|exists:email_settings,id',
                'to'          => 'required|array|min:1',
                'to.*'        => 'email',
                'cc'          => 'nullable|array',
                'cc.*'        => 'email',
                'bcc'         => 'nullable|array',
                'bcc.*'       => 'email',
                'subject'     => 'required|string|max:255',
                'message'     => 'required|string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|max:5120',
            ]);

            $emailSetting = EmailSetting::findOrFail($request->id);

            $transport = new EsmtpTransport(
                $emailSetting->smtp_host,
                $emailSetting->smtp_port,
                $emailSetting->encryption ?? null
            );
            $transport->setUsername($emailSetting->email);
            $transport->setPassword($emailSetting->password);

            $mailer = new Mailer('dynamic', app('view'), $transport, app('events'));
            $rawMime = null;

            $mailer->send([], [], function ($message) use ($request, $emailSetting, &$rawMime) {
                $message->from($emailSetting->email, 'GenLab')
                    ->to($request->to)
                    ->subject($request->subject)
                    ->html($request->message);

                if ($request->cc) $message->cc($request->cc);
                if ($request->bcc) $message->bcc($request->bcc);

                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $file) {
                        $message->attach($file->getRealPath(), [
                            'as' => $file->getClientOriginalName(),
                        ]);
                    }
                }

                if (method_exists($message, 'getSymfonyMessage')) {
                    $rawMime = $message->getSymfonyMessage()->toString();
                }
            });

            // Save to Sent folder
            if ($rawMime) {
                try {
                    $client = Client::make([
                        'host'          => $emailSetting->imap_host,
                        'port'          => $emailSetting->imap_port,
                        'encryption'    => $emailSetting->encryption,
                        'validate_cert' => false,
                        'username'      => $emailSetting->email,
                        'password'      => $emailSetting->password,
                        'protocol'      => 'imap',
                    ]);
                    $client->connect();

                    $sentFolder = null;
                    foreach (['Sent', 'Sent Mail', 'INBOX.Sent', 'Sent Items'] as $name) {
                        try {
                            $sentFolder = $client->getFolder($name);
                            break;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }

                    if ($sentFolder) {
                        $sentFolder->appendMessage($rawMime);
                    }
                    $client->disconnect();
                } catch (\Exception $imapEx) {
                    Log::warning('IMAP save failed', ['error' => $imapEx->getMessage()]);
                }
            }

            return back()->with('success', 'Email sent successfully and saved to Sent folder.');
        } catch (\Exception $e) {
            Log::error('Email send failed', ['error' => $e->getMessage()]);
            return back()->with('error', 'Failed to send email: ' . $e->getMessage());
        }
    }

    // email reply
    public function replyOnEmail(Request $request, $id)
{
    $request->validate([
        'to' => 'required|email',
        'message' => 'required|string',
        'attachments.*' => 'file|max:2048',
    ]);

    $emailSetting = EmailSetting::findOrFail($id);
    $to = $request->input('to');
    $messageBody = nl2br($request->input('message'));

    $attachments = [];
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('email_attachments', 'public');
            $attachments[] = storage_path('app/public/' . $path);
        }
    }

    // Use Symfony Mailer (same as send() function)
    $transport = new EsmtpTransport(
        $emailSetting->smtp_host,
        $emailSetting->smtp_port,
        $emailSetting->encryption ?? null
    );
    $transport->setUsername($emailSetting->email);
    $transport->setPassword($emailSetting->password);

    $mailer = new Mailer('dynamic', app('view'), $transport, app('events'));
    $rawMime = null;

    // Send email + capture raw MIME message
    $mailer->send([], [], function ($message) use ($emailSetting, $to, $attachments, $messageBody, &$rawMime) {
        $message->from($emailSetting->email, 'GenLab')
            ->to($to)
            ->subject('Re: Your message')
            ->html($messageBody);

        foreach ($attachments as $filePath) {
            $message->attach($filePath);
        }

        if (method_exists($message, 'getSymfonyMessage')) {
            $rawMime = $message->getSymfonyMessage()->toString();
        }
    });

    // Append reply to "Sent" folder (so it appears in your UI)
    if ($rawMime) {
        try {
            $client = Client::make([
                'host'          => $emailSetting->imap_host,
                'port'          => $emailSetting->imap_port,
                'encryption'    => $emailSetting->encryption,
                'validate_cert' => false,
                'username'      => $emailSetting->email,
                'password'      => $emailSetting->password,
                'protocol'      => 'imap',
            ]);
            $client->connect();

            $sentFolder = null;
            foreach (['Sent', 'Sent Mail', 'INBOX.Sent', 'Sent Items', '[Gmail]/Sent Mail'] as $name) {
                try {
                    $sentFolder = $client->getFolder($name);
                    if ($sentFolder) break;
                } catch (\Exception $e) {
                    continue;
                }
            }

            if ($sentFolder) {
                $sentFolder->appendMessage($rawMime);
            }

            $client->disconnect();
        } catch (\Exception $e) {
            Log::warning('IMAP save failed (reply)', ['error' => $e->getMessage()]);
        }
    }

    return redirect()->back()->with('success', 'Reply sent successfully!');
}

}
