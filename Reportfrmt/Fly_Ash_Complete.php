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
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements as per IS 3812(P-1):2013
            (Siliceous Fly Ash)
            </th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        <tr>
            <td colspan="6" style="font-weight:bold;">A. Physical Properties</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Fineness, m2/kg</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018</td>
            <td style="text-align: center;">320 Min.</td>
            <td style="text-align: center;"></td>
            <td style="text-align: center;"></td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Particles retained on 45micron IS sieve(wet sieving), %</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018</td>
            <td style="text-align: center;">34 Max.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Lime reactivity-Average Compressive Strength, N/mm2</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018
                </td>
            <td style="text-align: center;">4.5Min.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td style="text-align: left;">Compressive Strength at 28 days, %</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018              
                </td>
            <td style="text-align: center;">Not less than80%of the strength of corresponding plain cement mortar cubes</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Soundness by Autoclave Expansion, %</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018 & 
                IS:4031(P-3)-1988, RA 2019
                                               
                </td>
            <td style="text-align: center;">0.8 Max.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td colspan="6" style="font-weight:bold;">B. Chemical Properties</td>
        </tr>
        <tr>
            <td style="text-align: center;">1.</td>
            <td style="text-align: left;">Silicon Dioxide (SiO2)+Aluminium Oxide (Ai2O3)+Iron Oxide (Fe2O3), % by mass
</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018
RA 2019

</td>
            <td style="text-align: center;">70 Min.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">2.</td>
            <td style="text-align: left;">Silicon Dioxide (SiO2), % by mass</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018 
RA 2019

</td>
            <td style="text-align: center;">35 Min.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
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
          <th style="padding: 2px 9px; text-align: center; width:18%;">Requirements as per IS 3812(P-1):2013
            (Siliceous Fly Ash)
            </th>
          <th style="padding: 2px; text-align: center; width:12%;">Results</th>
          <th style="padding: 2px 5px; text-align: center; width:12%;">Conformity</th>
        </tr>
        
        <tr>
            <td style="text-align: center;">3.</td>
            <td style="text-align: left;">Reactive Silica, % by mass

</td>
            <td style="text-align: center;">IS:3812 (P-1)-2013, RA 2017

                </td>
            <td style="text-align: center;">20 Min.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">4.</td>
            <td style="text-align: left;">Magnesium Oxide (MgO), % by mass
</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018
               
                </td>
            <td style="text-align: center;">5.0 Max.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">5.</td>
            <td style="text-align: left;">Total Sulphur as Sulphur Tri Oxide(SO3), % by mass
</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018
                               
                </td>
            <td style="text-align: center;">3.0 Max.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">6.</td>
            <td style="text-align: left;">Available Alkalies as equivalent Sodium Oxide (Na2O), % by mass
</td>
            <td style="text-align: center;">IS:4032-1985, RA 2019 & 
                IS:3812 (P-1)-2013, RA 2017
                
                        
                </td>
            <td style="text-align: center;">1.5 Max.</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">7.</td>
            <td style="text-align: left;">Total Chlorides, % by mass
</td>
            <td style="text-align: center;">IS:4032-1985, RA 2019
                                      
                </td>
            <td style="text-align: center;">0.05 Max.
</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
        <tr>
            <td style="text-align: center;">8.</td>
            <td style="text-align: left;">Loss on Ignition, % by mass
</td>
            <td style="text-align: center;">IS:1727-1967, RA 2018
                                      
                </td>
            <td style="text-align: center;">5.0 Max.
</td>
            <td style="text-align: center;"> </td>
            <td style="text-align: center;"> </td>
        </tr>
                
      </table>    
      
</body>
</html>