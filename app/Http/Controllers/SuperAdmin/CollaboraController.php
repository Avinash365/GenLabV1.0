<?php
namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ReportFormat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CollaboraController extends Controller
{
    public function open(ReportFormat $reportFormat)
    {
        abort_unless(config('collabora.enabled'), 404);
        $ext = strtolower(pathinfo($reportFormat->stored_file_name, PATHINFO_EXTENSION));
        if(!in_array($ext,['doc','docx','odt'])) abort(400,'Unsupported file');

    // Build WOPI src using a base reachable from the Collabora container
    $publicBase = rtrim(config('collabora.wopi_public_base'), '/');
    $wopiSrc = $publicBase.'/wopi/files/'.$reportFormat->id;
    $ttl = (int)config('collabora.token_ttl');
    $token = $this->makeToken(['fid'=>$reportFormat->id,'exp'=>time()+$ttl]);
    $serverUrl = rtrim(config('collabora.server_url'), '/');
    return view('superadmin.reporting.report-formats.collabora-edit', compact('reportFormat','serverUrl','wopiSrc','token','ttl'));
    }

    protected function makeToken(array $payload): string
    {
        $secret = config('collabora.secret');
        $json = json_encode($payload);
        $sig = base64_encode(hash_hmac('sha256',$json,$secret,true));
        return base64_encode($json.'.'.$sig);
    }

    protected function decodeToken(string $token): ?array
    {
        $raw = base64_decode($token, true);
        if(!$raw) return null;
        if(!str_contains($raw,'.')) return null;
        [$json,$sig] = explode('.', $raw,2);
        $secret = config('collabora.secret');
        $calc = base64_encode(hash_hmac('sha256',$json,$secret,true));
        if(!hash_equals($calc,$sig)) return null;
        $data = json_decode($json,true); if(!$data) return null;
        if(($data['exp']??0) < time()) return null;
        return $data;
    }

    // Minimal WOPI endpoints
    public function checkFileInfo($id, Request $request)
    {
        abort_unless(config('collabora.enabled'), 404);
        $file = ReportFormat::findOrFail($id);
        $diskPath = Storage::disk('public')->path('report-formats/'.$file->stored_file_name);
        if(!is_file($diskPath)){
            $alt = public_path('storage/report-formats/'.$file->stored_file_name);
            if(is_file($alt)) $diskPath = $alt; else abort(404);
        }
        return [
            'BaseFileName' => $file->original_file_name,
            'Size' => filesize($diskPath),
            'Version' => (string)$file->updated_at?->timestamp,
            'SupportsLocks' => false,
            'SupportsUpdate' => true,
            'UserCanWrite' => true,
            'UserFriendlyName' => auth()->user()->name ?? 'User',
        ];
    }

    public function getFile($id)
    {
        abort_unless(config('collabora.enabled'), 404);
        $file = ReportFormat::findOrFail($id);
        $diskPath = Storage::disk('public')->path('report-formats/'.$file->stored_file_name);
        if(!is_file($diskPath)){
            $alt = public_path('storage/report-formats/'.$file->stored_file_name);
            if(is_file($alt)) $diskPath = $alt; else abort(404);
        }
        return response()->file($diskPath); // streams
    }

    public function putFile($id, Request $request)
    {
        abort_unless(config('collabora.enabled'), 404);
        $file = ReportFormat::findOrFail($id);
        $diskPath = Storage::disk('public')->path('report-formats/'.$file->stored_file_name);
        if(!is_file($diskPath)){
            $alt = public_path('storage/report-formats/'.$file->stored_file_name);
            if(is_file($alt)) $diskPath = $alt; else abort(404);
        }
        $content = $request->getContent();
        file_put_contents($diskPath, $content);
        $file->touch();
        return response('',200);
    }
}
