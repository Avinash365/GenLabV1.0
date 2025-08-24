<?php
session_start();
// Jika tidak bisa login maka balik ke login.php
// jika masuk ke halaman ini melalui url, maka langsung menuju halaman login
if (!isset($_SESSION['login'])) {
    header('location:login.php');
    exit;
}

// Memanggil atau membutuhkan file function.php
require 'function.php';

// Mengambil data dari nis dengan fungsi get
$JOB_CARD_NO = $_GET['JOB_CARD_NO'];


// Mengambil data dari table siswa dari nis yang tidak sama dengan 0
$nonulr = query("SELECT * FROM nonulr WHERE `JOB_CARD_NO`='" . $JOB_CARD_NO . "'")[0];

// Jika fungsi ubah lebih dari 0/data terubah, maka munculkan alert dibawah


error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
header("content-type: application/vnd.ms-word");
header("content-Disposition: attachment; Filename=Report.doc");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <style>
        table, th {
          border: 1px solid black;
          border-collapse: collapse;
          text-align: left;
          word-wrap: break-word;
          font-weight: bold;
          overflow: hidden;
          font-size: 14.5px;
          font-family: 'Times New Roman', Times, serif;
          padding: 2px;
        }
        p {
            text-align: right;
            font-weight: bold;
        }
        td {
          border: 1px solid black;
          border-collapse: collapse;
          text-align: left;
          word-wrap: break-word;
          font-weight: normal;
          overflow: hidden;
          font-size: 14.5px;
          font-family: 'Times New Roman', Times, serif;
          padding: 2px 4px;
        }
        </style>
        <?php
        header("content-type: application/vnd.ms-word");
        header("content-Disposition: attachment; Filename=Report.doc");
        ?>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Table</title>
</head>
<body>
<p style="margin-bottom:-30px; margin-top: 50;">LR 1404</p>
    <p>Page 1 of 2</p>
<div style=" margin-left:auto;margin-right:auto;">
    
<table style=" width: 110%; margin: left 10px;margin: right 10px; align-self: center;">
        <tr>
          <th colspan="3" style="width: 65%; text-align: left;">REPORT NO.  <?= $nonulr['JOB_CARD_NO']; ?></th>
          <th colspan="3" style="text-align: left;">ULR No.: <?= $nonulr['ULR_NO']; ?></th>
        </tr>
        <tr>
            <th rowspan="2" style="width: 18.3%;">Issued To</th>
            <td rowspan="2">:</td>
            <td rowspan="2" style="font-weight:bold;"><?= $nonulr['ISSUED_TO']; ?>
                
                
            <th>
                Date of Receipt
            </th>
            <td>:</td>
            <td style="font-weight:bold;">
                <?= $nonulr['JOB_ORDER_DATE']; ?>
            </td>
        </tr>
        <tr>
            <th>Date of Start of Analysis</th>
            <td>:</td>
            <td style="font-weight:bold;"><?= $nonulr['JOB_ORDER_DATE']; ?></td>
        </tr>
        <tr>
            <th style="width: 18.3%;">Letter Ref. No. & Date</th>
            <td style="width: .7%">:</td>
            <td style="font-weight:bold;"><?= $nonulr['REFRENCE_NO']; ?>
                
                </td>
            <th>
                Date of Completion of Analysis
            </th>
            <td>:</td>
            <td style="font-weight:bold;"><?= $nonulr['ISSUE_DATE']; ?></td>
        </tr>
        <tr>
            <th style="width: 18.3%;">Sample Description
                </th>
            <td>:</td>
            <td style="font-weight:bold;"><?= $nonulr['SAMPLE_DISCRIPTION']; ?> 
            
                  </td>
            <th>Date of Issue</th>
            <td>:</td>
            <td style="font-weight:bold;"><?= $nonulr['ISSUE_DATE']; ?></td>
        </tr>
        <tr>
            <th style="width: 18.3%;">Name of Work</th>
            <td style="width: .7%;">:</td>
            <td colspan="4" style="font-weight:bold;"><?= $nonulr['NAME_OF_WORK']; ?>
            </td>
            
        </tr>
        <tr>
              <th style="width: 18.3%;">Agency</th>
              <td style="width: .7%;">:</td>
              <td colspan="4" style="font-weight:bold;"><?= $nonulr['CONTRACTOR']; ?>
              </td>
              
          </tr>
      </table>
    </div><br>
    <table style="width: 110%; margin-left:auto;margin-right:;">
        <tr>
          <th style="padding: 2px; text-align: center; width:8%;">S.No.</th>
          <th style="padding: 2px; text-align: center; width:30%; ">Tests</th>
          <th style="padding: 2px 6px; text-align: center; width:20%; ">Test Methods</th>
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements as per <br>IS : 269-2015</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td colspan="6" style="font-weight:bold;">Physical Requirement</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Consistency, %</td>
            <td style="text-align: center;">IS:4031(P-4)-1988, 
                RA 2019</td>
            <td style="text-align: center;">-</td>
            <td style="text-align: center;">29.1</td>
            <td style="text-align: center;">-</td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Density, g/cc</td>
            <td style="text-align: center;">IS:4031(P-11)-1988,RA 2019</td>
            <td style="text-align: center;">-</td>
            <td style="text-align: center;">3.16</td>
            <td style="text-align: center;">-</td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Fineness, m2/ kg</td>
            <td style="text-align: center;">IS:4031(P-2)-1999, 
                RA 2018
                </td>
            <td style="text-align: center;">225 Min.</td>
            <td style="text-align: center;">276</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td style="text-align: left;">Initial Setting Time, Minutes</td>
            <td style="text-align: center;">IS:4031(P-5)-1988, 
                RA 2019               
                </td>
            <td style="text-align: center;">30 Min.</td>
            <td style="text-align: center;">145</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Final Setting Time, Minutes</td>
            <td style="text-align: center;">IS:4031(P-5)-1988, 
                RA 2019                               
                </td>
            <td style="text-align: center;">600 Max.</td>
            <td style="text-align: center;">255</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Soundness By Le Chatelier Expension, mm</td>
            <td style="text-align: center;">IS:4031(P-3)-1988, 
                RA 2019                        
                </td>
            <td style="text-align: center;">10 Max.</td>
            <td style="text-align: center;">1.5</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Soundness By Autoclave Expension, %</td>
            <td style="text-align: center;">IS:4031(P-3)-1988, 
                RA 2019                                      
                </td>
            <td style="text-align: center;">0.8 Max.</td>
            <td style="text-align: center;">0.08</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">8.</td>
            <td style="text-align: left;">Compressive Strength at
                3 Days (72±1 Hours), MPa
                </td>
            <td style="text-align: center;">IS:4031(P-6)-1988, 
                RA 2019
                                                      
                </td>
            <td style="text-align: center;">23 Min.</td>
            <td style="text-align: center;">24.5</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">9.</td>
            <td style="text-align: left;">Compressive Strength at
                7 Days (168±2 Hours), MPa                
                </td>
            <td style="text-align: center;">IS:4031(P-6)-1988, 
                RA 2019           
                                                      
                </td>
            <td style="text-align: center;">33 Min.</td>
            <td style="text-align: center;">36.5</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">10.</td>
            <td style="text-align: left;">Compressive Strength at
                28 Days (672±4 Hours), MPa                                
                </td>
            <td style="text-align: center;">IS:4031(P-6)-1988, 
                RA 2019
                                                
                </td>
            <td style="text-align: center;">43 - 58</td>
            <td style="text-align: center;">Awaited </td>
            <td style="text-align: center;">-</td>
        </tr>
        
      </table>
      <br>
      <div style="page-break-after: always;"></div>

    <div style=" margin-left:auto;margin-right:auto;margin-top:50px;">
        <p style="margin-top: 100px;">LR 1404</p>
    <p>Page 2 of 2</p>
    
    <table style=" width: 110%; margin: left 10px;margin: right 10px; align-self: center;">
    <tr>
            <th colspan="3" style="width: 65%; text-align: left;">REPORT NO.  <?= $nonulr['JOB_CARD_NO']; ?></th>
            <th colspan="3" style="text-align: left;">ULR No.: <?= $nonulr['ULR_NO']; ?></th>
          </tr>
          <tr>
              <th rowspan="2" style="width: 18.3%;">Issued To</th>
              <td rowspan="2">:</td>
              <td rowspan="2" style="font-weight:bold;"><?= $nonulr['ISSUED_TO']; ?>
                  
                  
              <th>
                  Date of Receipt
              </th>
              <td>:</td>
              <td style="font-weight:bold;">
                  <?= $nonulr['JOB_ORDER_DATE']; ?>
              </td>
          </tr>
          <tr>
              <th>Date of Start of Analysis</th>
              <td>:</td>
              <td style="font-weight:bold;"><?= $nonulr['JOB_ORDER_DATE']; ?></td>
          </tr>
          <tr>
              <th style="width: 18.3%;">Letter Ref. No. & Date</th>
              <td style="width: .7%">:</td>
              <td style="font-weight:bold;"><?= $nonulr['REFRENCE_NO']; ?>
                  
                  </td>
              <th>
                  Date of Completion of Analysis
              </th>
              <td>:</td>
              <td style="font-weight:bold;"><?= $nonulr['ISSUE_DATE']; ?></td>
          </tr>
          <tr>
              <th style="width: 18.3%;">Sample Description
                  </th>
              <td>:</td>
              <td style="font-weight:bold;"><?= $nonulr['SAMPLE_DISCRIPTION']; ?> 
                  
                    </td>
              <th>Date of Issue</th>
              <td>:</td>
              <td style="font-weight:bold;"><?= $nonulr['ISSUE_DATE']; ?></td>
          </tr>
        
      </table>
    </div><br>
    <table style="width: 110%; margin-left:auto;margin-right:;">
        <tr>
          <th style="padding: 2px; text-align: center; width:8%;">S.No.</th>
          <th style="padding: 2px; text-align: center; width:30%; ">Tests</th>
          <th style="padding: 2px 6px; text-align: center; width:20%; ">Test Methods</th>
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements as per <br>IS : 269-2015</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td colspan="6" style="font-weight:bold;">Chemical Requirement</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Ratio of % of lime to 
% of SiO2, Al2O3 & Fe2O3 as per formula<br>
(_CaO__-__0.7So3____)<br> 2.8SiO2+1.2Al2O3+0.65
Fe2O3

</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019

</td>
            <td style="text-align: center;">0.66 -1.02</td>
            <td style="text-align: center;">0.89</td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Ratio of % of Alumina to that of Iron oxide </td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019

</td>
            <td style="text-align: center;">0.66 Min.</td>
            <td style="text-align: center;">1.17</td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Insoluble Residue, 
(% by mass)

</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019

                </td>
            <td style="text-align: center;">5.0 Max.</td>
            <td style="text-align: center;">2.6</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td style="text-align: left;">Magnesia as MgO, 
(% by mass)
</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019
               
                </td>
            <td style="text-align: center;">6.0 Max.</td>
            <td style="text-align: center;">2.96</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Total Sulphur Content Calculated as Sulphuric Anhydride (SO3),
 ( % by mass)
</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019
                               
                </td>
            <td style="text-align: center;">3.5 Max.</td>
            <td style="text-align: center;">2.01</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Loss on Ignition
 (% by mass)
</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019
                        
                </td>
            <td style="text-align: center;">5.0 Max.</td>
            <td style="text-align: center;">2.4</td>
            <td style="text-align: center;">Yes </td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Chloride Content
(as Cl), % by mass
</td>
            <td style="text-align: center;">IS: 4032-1985, 
RA 2019
                                      
                </td>
            <td style="text-align: center;">0.1 Max.,
0.05 Max.(For Prestressed Structures)
</td>
            <td style="text-align: center;">0.018</td>
            <td style="text-align: center;">Yes </td>
        </tr>
                
      </table>    
      <span style="font-size:11px;">*Any Deviation from the standard test/ method/specification Nil.</span>

</body>
</html>