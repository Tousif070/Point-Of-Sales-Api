<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Email</title>
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body{
            font-family: sans-serif;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529;
            background-color: #fff;
            -webkit-text-size-adjust: 100%;
            -webkit-tap-highlight-color: transparent;
        }
        .fw-bold{
            font-weight: 700;
        }
        .fw-semibold{
            font-weight: 600;
        }
        .text-muted{
            color: #6c757d;
            margin-top: 1rem;
        }

        .img{
            height: 40px;
            margin-bottom: 0.5rem;
        }
        .wrapper{
            background-color: #ededed;
            height: 100vh;
        }
        .main-wrapper{
            height: fit-content;
            width: 30rem;
            background-color: #fff;
            margin: auto;
        }
        .top h4{
            background-color: #044867;
            padding: 20px 0;
            color: #fff;
            margin: 0;
            text-align: center;
            font-size: 1.25rem;
            font-weight: 500;
            line-height: 1.2;
        }
        .img img {
            height: 100%;
            width: 100%;
            object-fit: contain;
        }
        .address{
            text-align: center;
            font-size: 13px;
        }
        .middle-1{
            font-size: 1rem;
            padding: 0.5rem 1.5rem 1.5rem 1.5rem;
            margin-bottom: 0.5rem;
            margin-top: 1.5rem;
        }
        .middle-2{
            background-color: #f8f9fa;
            padding-right: 0;
            padding: 1.5rem;

        }
        .footer{
            padding-bottom: 2rem;
            padding-top: 1rem;
        }

        @media (max-width: 576px){
            .main-wrapper{
                width: 20rem;
            }
        }
    </style>
    
</head>

<body>
    <div class="wrapper">
        <div class="main-wrapper">
            <div class="top text-center">
                <h4 class="fw-semibold">SMARTPHONE DEPOT INC.</h4>
            </div>
            <div class="middle-1">
                <p class="fw-semibold">Dear Customer,</p> 
                <p>Thank you for shopping with us. <br> Please find your invoice in the attachment.</p>
                <p class="text-muted">Regards, <br>Smartphone Depot Inc.</p>
            </div>
            <div class="middle-2">
                <p class="fw-semibold">Details</p> 
                <p>Name - {{ $name }}</p>
                @if (!empty($business_name))
                    <p>Business Name - {{ $business_name }}</p>
                @endif
                <p>Invoice No - {{ $invoice_no }}</p>
                <p>Total - ${{ $total }}</p>
                <p>Payment Status - {{ $payment_status }}</p>
            </div>
            <div class="footer">
                <div class="img">
                    <img src="{{ asset('/public/assets/images/logo/logo.png') }}" alt="">
                </div>
                <div class="address">
                    <p class="fw-semibold">2735 Hartland Road. Suite 303 <br> Falls Church, VA 22043</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>