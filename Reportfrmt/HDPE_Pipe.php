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
<p style="margin-bottom:-30px; margin-top: 100;">LR 1404</p>
    <p>Page 1 of 3</p>
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
IS 4984:2016 
With Amendment No. 1,2

</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Visual Appearance</td>
            <td style="text-align: center;">IS 4984:2016, RA 2021</td>
            <td style="text-align: center;">The Internal and External surface of the pipe shall be smooth, clean and free from grooving and other defects.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Colour</td>
            <td style="text-align: center;">IS 4984:2016, RA 2021</td>
            <td style="text-align: center;">The Colour of the Pipe shall be black with blue identification stripes </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;" rowspan="4">3.</td>
            <td style="text-align: left;" colspan="5">Dimensions, mm</td>
        </tr>
        <tr>
            <td style="text-align: left;">i). Mean Outside Diameter </td>
            <td style="text-align: left;" rowspan="3">IS 4984:2016, RA 2021</td>
            <td style="text-align: center;">
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: left;">ii). Out of Roundness (Ovality) </td>
            <td style="text-align: center;">
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: left;">iii). Wall Thickness  </td>
            <td style="text-align: center;">
                </td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"> </td>
        </tr>
        </table>
        <div style="page-break-after: always;"></div>
        <br><br>

<div style=" margin-left:auto;margin-right:auto;margin-top:50px;">
    <p style="margin-top: 100px;">LR 1404</p>
<p>Page 2 of 3</p>

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
IS 4984:2016 
With Amendment No. 1,2

</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;" rowspan="2">4.</td>
            <td style="text-align: left;">Hydraulic Pressure Test, MPa
                Internal Pressure Creep Rupture Test,<br>
                i). At 27̊ C for 100 hours, Induced Stress 10.7 MPa
                </td>
            <td style="text-align: center;" rowspan="2">IS 4984:2016, RA 2021                        
                </td>
            <td style="text-align: center;">The pipes under test shall show no sign of localized swelling, leakage or warping and shall not burst during the test period</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: left;">ii). At 80̊ C for 48 hours, Induced Stress at 5.7 MPa 
                </td>
            <td style="text-align: center;">The pipes under test shall show no sign of localized swelling, leakage or warping and shall not burst during the test period
                                                      
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Longitudinal Reversion Test, %</td>
            <td style="text-align: center;">IS 4984:2016, RA 2021</td>
            <td style="text-align: center;">Longitudinal reversion shall not be greater than 3 percent.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Melt Flow Rate, g/10 minute
                (At 190̊ C temp. with nominal load of 5 kgf)
                </td>
            <td style="text-align: center;">IS 2530:1963, RA 2018</td>
            <td style="text-align: center;">0.20-1.10</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Density kg/m3
                </td>
            <td style="text-align: center;">IS 7328:2020</td>
            <td style="text-align: center;">930-960</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">8.</td>
            <td style="text-align: left;">Overall Migration Test, mg/dm2
                </td>
            <td style="text-align: center;">IS 9845:1998, RA 2020</td>
            <td style="text-align: center;">10.0 Max.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        </table>
        <div style="page-break-after: always;"></div>

<div style=" margin-left:auto;margin-right:auto;margin-top:50px;">
    <p style="margin-top: 100px;">LR 1404</p>
<p>Page 3 of 3</p>

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
IS 4984:2016 
With Amendment No. 1,2

</th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td style="text-align: center;">9.</td>
            <td style="text-align: left;">Carbon Black Content, %
                </td>
            <td style="text-align: center;">S 2530:1963, RA 2018</td>
            <td style="text-align: center;">The carbon black content shall be within 2.5±0.5%.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">10.</td>
            <td style="text-align: left;">Carbon Black Dispersion 
                </td>
            <td style="text-align: center;">IS 2530:1963, RA 2018</td>
            <td style="text-align: center;">The dispersion of carbon black shall be satisfactory.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
                
      </table>    

</body>
</html>