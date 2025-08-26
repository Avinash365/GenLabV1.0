<!DOCTYPE html>
<html>
<head>
    <style>
        @page {
            size: A4;
            margin: 15mm;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 0;
            background: #f9f9f9;
        }
        .page {
            width: 100%;
            min-height: 297mm;
            padding: 0;
            box-sizing: border-box;
            page-break-after: always;
            display: flex;
            justify-content: center;
        }
        .card {
             border: 1px solid #000;
            padding: 12px;
            width: 100%; /* full width of page container */
            min-height: 300px; /* reduce height to fit 2 cards in one page */
            box-sizing: border-box;
            background: #fff;
            margin-bottom: 20px; /* smaller gap */
            page-break-inside: avoid; /* prevent splitting inside card */
        }
        .header-row {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 1px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        .lab-logo {
            margin-top:-20px;
            height: 100px;
            width: 100px;
            object-fit: contain;
        }
        .lr-no1{
            margin-top:-80px; 
            font-size: 12px;
            line-height: 1.5;
            font-weight: bold;
            text-align: right; 
        }
        
        .lr-no {    
            font-size: 12px;
            line-height: 1.5;
            font-weight: bold;
            text-align: left; 
            margin-bottom: 10px;
        }
        .lr-no1 .value {
            display: inline-block;
            min-width: 120px; 
            border-bottom: 1px solid black;
            font-weight: normal;
            padding: 0 2px;
        }

       .table{
        margin-top:40px; 
       }
        .lr-no span {
            display: inline-block;
            /* width: calc(100% - 160px);  */
            border-bottom: 1px solid black;
            font-weight: normal;
            padding-left: 20px;
            word-break: break-word;
            white-space: normal;
            vertical-align: bottom;
        }
        .value1{
            width: calc(100% - 110px); 
        }
        .value2{
            width: calc(100% - 120px); 
        }
        .value3{
            width: calc(100% - 145px); 
        }
        .value4{
            width: calc(100% - 128px); 
        }
         .value5{
            width: calc(100% - 95px); 
        }
         .value6{
            width: calc(100% - 78px); 
        }

        .footer {
            text-align: right;
            margin-top: 80px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
     
     
    <div class="page">
        <div class="card">
            <div class="header-row">
                INDIAN TESTING LABORATORY
            </div>

            <div class="">
                <img class="lab-logo" src="{{ asset('assets/img/bookingCardlogo/bookingCardlogo.png') }}" alt="Logo">
                <div class="lr-no1">
                     <p>LR 785499<p> <br>
                     Expected Lab Date:- <span class="value">22/04/2003</span>
                </div>
            </div>

            <div class="table">
                <div class="lr-no">
                     Job Order No :-<span class="value1">22/04/2003</span>
                </div> 
                <div class="lr-no">
                    Job Order Date :-<span class="value2">22/04/2003</span>
                </div> 
                <div class="lr-no">
                    Sample Description :-<span class="value3">ihgkjndsfkdsnlfdslklfds with a very very</span>
                </div> 
                <div class="lr-no">
                    Sample Quantity :-<span class="value4">50 Pieces</span>
                </div> 
                <div class="lr-no">
                    Particulars :-<span class="value5">Some really long text that needs to wrap neatly in</span>
                </div> 
                <div class="lr-no">
                   Amount :-<span class="value6">784546</span>
                </div> 
            </div>

            <div class="footer">
                Authorised Signatory
            </div>
        </div> 
    </div>

</body>
</html>
