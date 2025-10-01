<div align="center" style="padding-top:150px;">
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
                <td width="160" rowspan="2" style="width: 160px; padding: 0px 7px; height: 7px; border: 1px solid black;">ISSUED TO</td>
                <td width="19" rowspan="2" valign="top" style="width: 19px; padding: 0px 7px; height: 7px; border: 1px solid black;">:</td>
                <td width="283" rowspan="2" valign="top" style="width: 282px; padding: 0px 7px; height: 7px; text-transform: uppercase; border: 1px solid black;">
                    {!! nl2br(e($issued_to)) !!}
                </td>
                <td width="151" style="width: 150px; padding: 0px 7px; height: 7px; border: 1px solid black;">DATE OF RECEIPT</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 7px; border: 1px solid black;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 7px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ $date_of_receipt }}</td>
            </tr>

            <tr style="height: 5px;">
                <td width="151" style="width: 150px; padding: 0px 7px; height: 5px; border: 1px solid black;">DATE OF START OF ANALYSIS</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ $date_of_start_analysis }}</td>
            </tr>

            <tr style="height: 12px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 12px; border: 1px solid black;">LETTER REF. NO. & DATE</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 12px; border: 1px solid black;">:</td>
                <td width="283" valign="top" style="width: 282px; padding: 0px 7px; height: 12px; text-transform: uppercase; border: 1px solid black;">
                    {!! nl2br(e($letter_ref . '&' . $letter_ref_date)) !!}
                </td>                
                <td width="151" style="width: 150px; padding: 0px 7px; height: 12px; border: 1px solid black;">DATE OF COMPLETION OF ANALYSIS</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 12px; border: 1px solid black;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 12px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ $date_of_completion }}</td>
            </tr>

            <tr style="height: 5px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 5px; border: 1px solid black;">SAMPLE DESCRIPTION</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                <td width="283" style="width: 282px; padding: 0px 7px; height: 5px; text-transform: uppercase; border: 1px solid black;">{!! nl2br(e($sample_description)) !!}</td>
                <td width="151" style="width: 150px; padding: 0px 7px; height: 5px; border: 1px solid black;">DATE OF ISSUE</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center; text-transform: uppercase; border: 1px solid black;">{{ $date_of_issue }}</td>
            </tr>

            <tr style="height: 5px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 5px; border: 1px solid black;">NAME OF WORK</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px; border: 1px solid black;">:</td>
                <td width="539" colspan="4" style="width: 536px; padding: 0px 7px; height: 5px; text-transform: uppercase; border: 1px solid black;">{!! nl2br(e($name_of_work)) !!}</td>
            </tr>
        </tbody>
    </table>
</div>
