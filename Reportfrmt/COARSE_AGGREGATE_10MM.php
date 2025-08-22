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
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements 
            as per <br>
            IS : 383-2016<br>
            With Amendment No. 1,2
            
</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;" colspan="5">Sieve Analysis, Material Passing Through IS Sieve,%</td>
            
        </tr>
        <tr>
            <td style="text-align: center;">a)</td>
            <td style="text-align: left;">12.5 mm</td>
            <td style="text-align: center;" rowspan="4">IS:2386(P-1)-1963, RA 2021</td>
            <td style="text-align: center;">100</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">b)</td>
            <td style="text-align: left;">10 mm</td>
            <td style="text-align: center;">85-100
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">c)</td>
            <td style="text-align: left;">4.75 mm</td>
            <td style="text-align: center;">0-20              
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">d)</td>
            <td style="text-align: left;">2.36 mm</td>
            <td style="text-align: center;">0-5              
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Specific Gravity</td>
            <td style="text-align: center;">IS:2386 (P-3)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">2.1- 3.2 </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Water Absorption,% </td>
            <td style="text-align: center;">IS:2386 (P-3)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">5 Max. </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td style="text-align: left;">Bulk Density, kg/ltr.</td>
            <td style="text-align: center;">IS:2386 (P-3)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">-</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Combined Flakiness & Elongation  Index, %</td>
            <td style="text-align: center;">IS:2386(P-1)-1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">40 Max.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Aggregate Crushing Value, %</td>
            <td style="text-align: center;">IS:2386(P-4)-1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">30 Max.
                (Wearing surface)
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Aggregate Impact Value, %</td>
            <td style="text-align: center;">IS:2386(P-4)-1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">30/45 Max.
                (Wearing surface/ Non Wearing surface)
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        </table>
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
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements 
            as per <br>
            IS : 383-2016 <br>
            With Amendment No. 1,2
            
</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;">8.</td>
            <td style="text-align: left;">Aggregate Abrasion Value
                (Los Angeles machine), %
                            </td>
            <td style="text-align: center;">IS:2386(P-4)-1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">30/50 Max.
                (Wearing surface/ Non Wearing surface)                
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">9.</td>
            <td style="text-align: left;" colspan="5">Soundness, %                                
                </td>
        </tr>
        <tr>
            <td style="text-align: center;">a.</td>
            <td style="text-align: left;">With Sodium Sulphate (Na2SO4)                               
                </td>
            <td style="text-align: center;">IS:2386 (P-5)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">12.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">b.</td>
            <td style="text-align: left;">With Magnesium Sulphate (MgSO4)                               
                </td>
            <td style="text-align: center;">IS:2386 (P-5)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">18.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">10.</td>
            <td style="text-align: left;" colspan="5">Deleterious Material,%                               
                </td>
        </tr>
        <tr>
            <td style="text-align: center;">a)</td>
            <td style="text-align: left;">Material finer than 75 µ IS Sieve,%                               
                </td>
            <td style="text-align: center;">IS:2386 (P-1)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">1.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">b)</td>
            <td style="text-align: left;">Clay Lumps,%                               
                </td>
            <td style="text-align: center;">IS:2386 (P-2)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">1.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">c)</td>
            <td style="text-align: left;">Coal & Lignite, %                              
                </td>
            <td style="text-align: center;">IS:2386 (P-2)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">1.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">d)</td>
            <td style="text-align: left;">Total Deleterious Material,%                             
                </td>
            <td style="text-align: center;">-
                                                
                </td>
            <td style="text-align: center;">2.0 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">11.</td>
            <td style="text-align: left;" colspan="5">Alkali Aggregate Reactivity                                     
                </td>
        </tr>
        <tr>
            <td style="text-align: center;">a)</td>
            <td style="text-align: left;">Dissolved  Silica 
                (mill moles /liter)
                                            
                </td>
            <td style="text-align: center;">IS:2386 (P-7)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">-               
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">b)</td>
            <td style="text-align: left;">Reduction in alkalinity
                (mill moles/liter)                  
                                            
                </td>
            <td style="text-align: center;" rowspan="2">IS:2386 (P-7)- 1963, RA 2021
                                                
                </td>
            <td style="text-align: center;">-               
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">c)</td>
            <td style="text-align: left;">Nature of Aggregate         
                                            
                </td>
            <td style="text-align: center;">Shall be Innocuous              
                </td>
                <td></td>
                <td></td>
        </tr>
        </table>
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
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements 
as per <br>
IS : 383-2016<br>
With Amendment No. 1,2
            
</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;">12</td>
            <td style="text-align: left;">Acid Soluble Chloride Content as Cl, % by mass                               
                </td>
            <td style="text-align: center;">IS: 4032-1985, 
                RA 2019                
                                                
                </td>
            <td style="text-align: center;">0.04 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        <tr>
            <td style="text-align: center;">13</td>
            <td style="text-align: left;">Total Sulphate Content as SO3, % by mass                               
                </td>
            <td style="text-align: center;">IS: 4032-1985, 
                RA 2019              
                                                
                </td>
            <td style="text-align: center;">0.5 Max.                
                </td>
                <td></td>
                <td></td>
        </tr>
        
      </table>

</body>
</html>