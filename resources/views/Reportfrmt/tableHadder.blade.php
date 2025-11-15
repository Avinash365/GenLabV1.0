<div style="position: relative; padding-top:110px; padding-left:15px; font-family: 'Times New Roman', Times, serif;">

    {{-- Top Bar (QR + Page Info in Same Row) --}}
    <table style="width:718px; border-collapse: collapse; font-family: 'Times New Roman', Times, serif;">
        <tr>
            {{-- QR Code (Left) --}}
            <td style="width:80px; padding-bottom:10px;">
                @if(!empty($qr_code_svg))
                    <img src="{{ $qr_code_svg }}" alt="QR Code" style="width:70px; height:70px;">
                @endif
            </td>

            {{-- Spacer --}}
            <td style="width:518px;"></td>

            {{-- Page Number + LR (Right) --}}
            <td style="width:120px; text-align:right; font-size:10pt; font-weight:bold; padding-right:5px; line-height:1.5;">
                LR - 1404<br>
                Page {PAGENO} of {nbpg}
            </td>
        </tr>
    </table>
    {{--  Report Header Table --}}
    @if(!isset($include_header) || $include_header == 1 )
        <div align="center" >
            <table class="MsoTableGrid" border="1" cellspacing="0" cellpadding="0" width="718"
                style="width: 72.3169%; margin-left: -2.18487%; margin-bottom:20px; font-weight: bold; text-transform: uppercase; border: 1px solid black; border-collapse: collapse;">
                <tbody>
                    <tr style="height: 6px;">
                        <td width="463" colspan="3" valign="top" style="width: 461px; padding: 0px 7px; height: 6px; border: 1px solid black;">
                            <p class="MsoHeader" style="margin: 0px; font-size: 15px; font-family: Aptos, sans-serif; font-weight: bold; text-transform: uppercase;">
                                <strong>REPORT NO.</strong>
                                <strong>{{ $report_no }}</strong>
                            </p>
                        </td>
                        <td width="255" colspan="3" valign="top" style="width: 254px; padding: 0px 7px; height: 6px; border: 1px solid black;">
                            <p class="MsoHeader" style="margin: 0px; font-size: 15px; font-family: Aptos, sans-serif; font-weight: bold; text-transform: uppercase;">
                                <strong>ULR No.</strong>
                                <strong>{{ $ulr_no }}</strong>
                            </p>
                        </td>
                    </tr> 
                    <tr style="height: 7px;">
                        <td width="160" rowspan="2" style="width: 160px; padding: 0px 7px; height: 7px; border: 1px solid black;">Issued To</td>
                        <td width="19" rowspan="2" valign="top" style="width: 19px; padding: 0px 7px; height: 7px; border: 1px solid black;">:</td>
                        <td width="283" rowspan="2" valign="top" style="width: 282px; padding: 0px 7px; height: 7px;  border: 1px solid black;">
                            {!! nl2br(e($issued_to)) !!}
                        </td>
                        <td width="151" style="width: 150px; padding: 0px 7px; height: 7px; border: 1px solid black;">Date of Receipt</td>
                        <td width="19" style="width: 19px; padding: 0px 7px; height: 7px; border: 1px solid black;">:</td>
                        <td width="85" style="width: 85px; padding: 0px 7px; height: 7px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ \Carbon\Carbon::parse($date_of_receipt)->format('d-m-Y') }}   </td>
                    </tr> 
                    <tr style="height: 5px;">
                        <td width="151" style="width: 150px; padding: 0px 7px; height: 5px; border: 1px solid black;">Date of start of analysis</td>
                        <td width="19" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                        <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ \Carbon\Carbon::parse($date_of_start_analysis)->format('d-m-Y') }}</td>
                    </tr> 
                    <tr style="height: 12px;">
                        <td width="160" style="width: 160px; padding: 0px 7px; height: 12px; border: 1px solid black;">Letter REF. NO. & Date</td>
                        <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 12px; border: 1px solid black;">:</td>
                        <td width="283" valign="top" style="width: 282px; padding: 0px 7px; height: 12px;  border: 1px solid black;">
                            {!! nl2br(e($letter_ref . ' & ' . $letter_ref_date)) !!}
                             <br>
                                {{ "Agence: " . $m_s }}
                        </td>                
                        <td width="151" style="width: 150px; padding: 0px 7px; height: 12px; border: 1px solid black;">Date of completion of analysis</td>
                        <td width="19" style="width: 19px; padding: 0px 7px; height: 12px; border: 1px solid black;">:</td>
                        <td width="85" style="width: 85px; padding: 0px 7px; height: 12px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ \Carbon\Carbon::parse($date_of_completion)->format('d-m-Y') }}</td>
                    </tr> 
                    <tr style="height: 5px;">
                        <td width="160" style="width: 160px; padding: 0px 7px; height: 5px; border: 1px solid black;">Sample Description</td>
                        <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                        <td width="283" style="width: 282px; padding: 0px 7px; height: 5px;  border: 1px solid black;">{!! nl2br(e($sample_description)) !!}</td>
                        <td width="151" style="width: 150px; padding: 0px 7px; height: 5px; border: 1px solid black;">Date of issue</td>
                        <td width="19" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                        <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ \Carbon\Carbon::parse($date_of_issue)->format('d-m-Y') }}</td>
                    </tr> 
                    <tr style="height: 5px;">
                        <td width="160" style="width: 160px; padding: 0px 7px; height: 5px; border: 1px solid black;">Name of work</td>
                        <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                        <td width="539" colspan="4" style="width: 536px; padding: 0px 7px; height: 5px;  border: 1px solid black;">{!! nl2br(e($name_of_work)) !!}</td>
                    </tr>
                </tbody>
            </table>  
        </div>
    @endif
</div>
