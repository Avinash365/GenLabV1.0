<div align="center" style="padding-top:150px;">
    <table class="MsoTableGrid" border="1" cellspacing="0" cellpadding="0" width="718" style="width: 72.3169%; margin-left: -2.18487%; margin-bottom:20px;">
        <tbody>
            <tr style="height: 6px;">
                <td width="463" colspan="3" valign="top" style="width: 461px; padding: 0px 7px; height: 6px;">
                    <p class="MsoHeader" style="margin: 0px; font-size: 15px; font-family: Aptos, sans-serif;">
                        <strong>REPORT NO.</strong>
                        <strong><?php echo e($report_no); ?></strong>
                    </p>
                </td>
                <td width="255" colspan="3" valign="top" style="width: 254px; padding: 0px 7px; height: 6px;">
                    <p class="MsoHeader" style="margin: 0px; font-size: 15px; font-family: Aptos, sans-serif;">
                        <strong>ULR No.</strong>
                        <strong><?php echo e($ulr_no); ?></strong>
                    </p>
                </td>
            </tr>

            <tr style="height: 7px;">
                <td width="160" rowspan="2" style="width: 160px; padding: 0px 7px; height: 7px;">
                    <strong>Issued To</strong>
                </td>
                <td width="19" rowspan="2" valign="top" style="width: 19px; padding: 0px 7px; height: 7px;">:</td>
                <td width="283" rowspan="2" valign="top" style="width: 282px; padding: 0px 7px; height: 7px;">
                    <?php echo nl2br(e($issued_to)); ?>

                </td>
                <td width="151" style="width: 150px; padding: 0px 7px; height: 7px;">Date of Receipt</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 7px;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 7px; text-align: center;"><?php echo e($date_of_receipt); ?></td>
            </tr>

            <tr style="height: 5px;">
                <td width="151" style="width: 150px; padding: 0px 7px; height: 5px;">Date of Start of Analysis</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 5px;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center;"><?php echo e($date_of_start_analysis); ?></td>
            </tr>

            <tr style="height: 12px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 12px;">Letter Ref. No. & Date</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 12px;">:</td>
                <td width="283" valign="top" style="width: 282px; padding: 0px 7px; height: 12px;"><?php echo nl2br(e($letter_ref)); ?></td>
                <td width="151" style="width: 150px; padding: 0px 7px; height: 12px;">Date of Completion of Analysis</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 12px;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 12px; text-align: center;"><?php echo e($date_of_completion); ?></td>
            </tr>

            <tr style="height: 5px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 5px;">Sample Description</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px;">:</td>
                <td width="283" style="width: 282px; padding: 0px 7px; height: 5px;"><?php echo nl2br(e($sample_description)); ?></td>
                <td width="151" style="width: 150px; padding: 0px 7px; height: 5px;">Date of Issue</td>
                <td width="19" style="width: 19px; padding: 0px 7px; height: 5px;">:</td>
                <td width="85" style="width: 85px; padding: 0px 7px; height: 5px; text-align: center;"><?php echo e($date_of_issue); ?></td>
            </tr>

            <tr style="height: 5px;">
                <td width="160" style="width: 160px; padding: 0px 7px; height: 5px;">Name of Work</td>
                <td width="19" valign="top" style="width: 19px; padding: 0px 7px; height: 5px;">:</td>
                <td width="539" colspan="4" style="width: 536px; padding: 0px 7px; height: 5px;"><?php echo nl2br(e($name_of_work)); ?></td>
            </tr>
        </tbody>
    </table>
</div>
<?php /**PATH A:\GenTech\htdocs\GenTech_bug\GenLab\resources\views/Reportfrmt/tableHadder.blade.php ENDPATH**/ ?>