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
          <th style="padding: 2px; text-align: center; ">Tests</th>
          <th style="padding: 2px 6px; text-align: center;">Test Methods</th>
          <th style="padding: 2px 9px; text-align: center;">Requirements as per  IS : 9537 (Part-3)-1983 With Amendment No. 1,2
            </th>
          <th style="padding: 2px; text-align: center;">Results</th>
          <th style="padding: 2px 5px; text-align: center;">Conformity</th>
        </tr>
        <tr>
            <td colspan="6" style="font-weight:bold;">Physical Requirement</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Marking</td>
            <td style="text-align: center;">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">Marking shall be durable and legible.
                Marking Shall be checked by inspection and by rubbing lightly the marking by hand for 15 second with a piece of cloth soaked with water and again for 15 seconds with a piece of cloth soaked with petroleum spirit.
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td colspan="5" style="font-weight:bold;">Dimension (Checking by Gauge)</td>
        </tr>
        <tr>
            <td style="text-align: center;">a.</td>
            <td style="text-align: left;">Maximum Outside Diameter</td>
            <td style="text-align: center;" rowspan="3">IS: 9537 (P-3)-1983, RA 2017</td>
            <td style="text-align: center;">It shall be possible to slide the appropriate gauge completely over the conduit, under its own weight.
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">b.</td>
            <td style="text-align: left;">Minimum Outside Diameter</td>
            <td style="text-align: center;">It shall not be possible to pass the gauge over the conduit, in any position, without undue force.
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">c.</td>
            <td style="text-align: left;">Minimum Inside Diameter</td>
            <td style="text-align: center;">It shall be Possible for the appropriate gauge to pass through the conduit under its own weight.
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Uniformity of the Wall Thickness</td>
            <td style="text-align: center;">IS: 9537 (P-3)-1983, RA 2017</td>
            <td style="text-align: center;">In no case shall the difference between the value measured and the average of the twelve values obtained from the three samples exceed 0.1mm + 10% of the average value.
                </td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td colspan="5" style="font-weight:bold;">Mechanical Properties</td>
        </tr>
        
      </table>
      <br><br>
      <div style="page-break-after: always;"></div>

    
    <p style="margin-top: 100px;">LR 1404</p>
    <p>Page 2 of 3</p>
    <div style=" margin-left:auto;margin-right:auto;margin-top:50px;">
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
          <th style="padding: 2px; text-align: center; ">Tests</th>
          <th style="padding: 2px 6px; text-align: center;">Test Methods</th>
          <th style="padding: 2px 9px; text-align: center;">Requirements as per  IS : 9537 (Part-3)-1983 With Amendment No. 1,2
            </th>
          <th style="padding: 2px; text-align: center;">Results</th>
          <th style="padding: 2px 5px; text-align: center;">Conformity</th>
        </tr>
        
        <tr>
            <td style="text-align: center;">a.</td>
            <td colspan="5" style="font-weight:bold;">Bending Test </td>
        </tr>
        <tr>
            <td style="text-align: center;">(i)</td>
            <td style="text-align: left;">At Room Temp.</td>
            <td style="text-align: center;" rowspan="2">IS: 9537 (P-3)-1983, RA 2017</td>
            <td style="text-align: center;" rowspan="2">After the test, the samples shall show no cracks visible to normal or corrected vision without magnification.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">(ii)</td>
            <td style="text-align: left;">At Low Temp</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;" rowspan="2">b.</td>
            <td style="text-align: left;" rowspan="2">Compression Test, %</td>
            <td style="text-align: center;" rowspan="2">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">The difference between the initial diameter and the diameter of the flattened sample shall then not exceed 10% of the outside diameter measured before the test.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">The difference between the initial diameter and the diameter of the flattened sample shall then not exceed 25% of the initial diameter.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Impact Test</td>
            <td style="text-align: center;">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">There shall be no sign of disintegration, neither shall there be any crack visible to the naked eye in at least nine of the twelve samples.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Collapse Test</td>
            <td style="text-align: center;">IS: 9537 (P-3)-1983, RA 2017</td>
            <td style="text-align: center;">It shall be possible to pass this gauge, through the conduit, fixed to support, under its own weight and without any initial speed.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Resistant to Heat, mm</td>
            <td style="text-align: center;">IS: 9537 (P-3)-1983, RA 2017</td>
            <td style="text-align: center;">The diameter of the impression shall not exceed 2 mm.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>
        <tr>
            <td style="text-align: center;">8.</td>
            <td style="text-align: left;">Resistance to Burning, sec.</td>
            <td style="text-align: center;">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">Any flame shall have died out in less than 30 seconds after removal of the burner.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;">Yes</td>
        </tr>     
        <tr>
            <td style="text-align: center;">9.</td>
            <td colspan="5" style="font-weight:bold;">Mechanical Properties</td>
        </tr>
        <tr>
            <td style="text-align: center;">a.</td>
            <td style="text-align: left;">Electrical Strength, At 2000V for 15 min</td>
            <td style="text-align: center;">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">No breakdown shall occur during the test.</td>
            <td style="text-align: center;"> </td> 
            <td style="text-align: center;">Yes</td> 
        </tr> 
      </table>    
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
          <th style="padding: 2px; text-align: center; ">Tests</th>
          <th style="padding: 2px 6px; text-align: center;">Test Methods</th>
          <th style="padding: 2px 9px; text-align: center;">Requirements as per  IS : 9537 (Part-3)-1983 With Amendment No. 1,2
            </th>
          <th style="padding: 2px; text-align: center;">Results</th>
          <th style="padding: 2px 5px; text-align: center;">Conformity</th>
        </tr>
        
         
        <tr>
            <td style="text-align: center;">b.</td>
            <td style="text-align: left;">Insulation Resistance, MΩ</td>
            <td style="text-align: center;">IS: 9537 (P-1)-1980, RA 2020</td>
            <td style="text-align: center;">The insulation resistance shall not be less than 100 MΩ.</td>
            <td style="text-align: center;"> </td> 
            <td style="text-align: center;">Yes</td> 
        </tr>
    </table>      

</body>
</html>