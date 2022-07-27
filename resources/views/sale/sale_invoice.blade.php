{{-- {{ $title }} <br>
{{ $date }} --}}


<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sale Invoice</title>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">

        <style>
            body{
                font-family: system-ui,"Segoe UI","Roboto","Helvetica Neue",Arial,"Noto Sans","Liberation Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";
            }
            h5, h6, p, th {
                font-size: 12px;
            }
            td {
                font-size: 11px;
            }
            .table thead tr{
                background-color: #dfe0e1;
            }
            .table td{
                padding: 0.5rem 0.5rem;
            }
        </style>

    </head>

    <body>
        <div class="pt-3" id="invoice_frame">
            <div class="">
                <div class="">
                    <div class="mb-0 pb-5">
                        <table class="table-borderless table-nowrap align-center pt-5 pb-3 table">
                            <tbody>
                                <tr>
                                    <td class="p-0">
                                        <div class="">
                                            <img src="{{ asset('/public/assets/images/logo/logo.png') }}" alt="logo light" height="30">
                                            <div class="mt-sm-5 mt-3">
                                                <h6 class="text-muted text-uppercase fw-semibold">Smartphone-Depot</h6>
                                                <p class="text-muted mb-1">2735 Hartland Road. Suite 303</p>
                                                <p class="text-muted mb-0">Falls Church, VA 22043</p>
                                            </div>
                                        </div>
                                    </td>
                                     <td class="p-0">
                                        <div>
                                            <div class="">
                                                <p class="text-muted mb-2 text-uppercase fw-bold">Invoice No: <span class="mb-0 fw-normal">{{ $sale_transaction->invoice_no }}</span></p>
                                                <p class="text-muted mb-2 text-uppercase fw-bold">Date: <span class="mb-0 fw-normal">{{ $sale_transaction->date }}</span></p>
                                                <p class="text-muted mb-2 text-uppercase fw-bold">Payment Status: <span class=" fw-normal">{{ $sale_transaction->payment_status }}</span></p>
                                                <p class="text-muted mb-2 text-uppercase fw-bold">Total: <span class="mb-0 fw-normal">${{ $sale_transaction->total_payable_after_sale_return }}</span></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-0">
                                        <div >
                                            <h6 class="text-muted fw-bold">Contact Information :</h6>
                                            <h6> <span class="text-muted fw-normal">Contact No:</span>  571-529-4022 </h6>
                                            <h6><span class="text-muted fw-normal">Email:</span> sales@smartphone-depot.com</h6>
                                            <h6 class="mb-0"><span class="text-muted fw-normal">Website:</span> <a class="link-primary" href="/https://www.smartphone-depot.com/"> www.smartphone-depot.com </a></h6>
                                        </div>
                                    </td>
                                   </tr>
                            </tbody>
                        </table>

                        <div class="pt-3 mb-4 border-top border-top-dashed">
                            <div class="">
                                <h6 class="text-muted text-uppercase fw-bold mb-1">Bill To</h6>
                                <p class="fw-semibold mb-1">{{ $sale_transaction->customer }}</p>
                                <p className="text-muted mb-1">{{ $sale_transaction->billing_address }}</p>
                            </div>
                        </div>

                        <div class="table-responsive"><h5 class="fw-bold mb-2">Products:</h5>
                            <table class="table-borderless table-nowrap align-center mb-0 table">
                                <thead>
                                    <tr class="table-active">
                                        <th scope="" style="width: 50px;">#</th>
                                        <th scope="">Product Name</th>
                                        <th scope="">Quantity</th>
                                        <th scope="">Unit Price ($)</th>
                                        <th scope="" class="text-end">Subtotal ($)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($product_summary as $product)
                                        <tr>
                                            <th scope="">{{ $loop->iteration }}</th>
                                            <td class="text-start">{{ $product->name }}</td>
                                            <td>{{ $product->quantity }}</td>
                                            <td>{{ $product->unit_price }}</td>
                                            <td class="text-end">{{ $product->quantity * $product->unit_price }}</td>
                                        </tr>
                                    @endforeach

                                    <tr class="border-top border-top-dashed mt-2">
                                        <td colspan="3"></td>
                                        <td colspan="2" class="fw-medium p-0">
                                            <table class="table-borderless text-start table-nowrap align-middle mb-0 table">
                                                <tbody>
                                                    @if ($sale_transaction->sale_return > 0)
                                                        <tr>
                                                            <th scope="">Total Payable After Sale Return</th>
                                                            <td class="text-end">${{ $sale_transaction->total_payable_after_sale_return }}</td>
                                                        </tr>
                                                    @else
                                                        <tr>
                                                            <td>Total Amount</td>
                                                            <td class="text-end">${{ $sale_transaction->total }}</td>
                                                        </tr>
                                                    @endif
                                                    
                                                    <tr>
                                                        <th>Total Paid</th>
                                                        <td class="text-end">${{ $sale_transaction->paid }}</td>
                                                    </tr>
                                                    @if ($sale_transaction->sale_return > 0)
                                                        <tr class="border-top border-top-dashed">
                                                            <th scope="">Total Remaining</th>
                                                            @if ($sale_transaction->total_payable_after_sale_return - $sale_transaction->paid < 0)
                                                                <td class="text-end">$0</td>
                                                            @else
                                                                <td class="text-end">${{ $sale_transaction->total_payable_after_sale_return - $sale_transaction->paid }}</td>
                                                            @endif
                                                            
                                                        </tr>
                                                        <tr>
                                                            <th scope="">Amount Credited</th>
                                                            <td class="text-end">${{ $sale_transaction->amount_credited }}</td>
                                                        </tr>
                                                    @else
                                                        <tr class="border-top border-top-dashed">
                                                            <th scope="">Total Remaining</th>
                                                            <td class="text-end">${{ $sale_transaction->total - $sale_transaction->paid }}</td>
                                                        </tr>
                                                    @endif

                                                    
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-5"><h5 class="fs-16 mb-2">Payment Information:</h5>
                            <div class="p-0">
                                <table class="table-borderless table-nowrap align-center mb-0 table">
                                    <thead>
                                        <tr class="table-active">
                                            <th scope="" style="width: 50px;">#</th>
                                            <th scope="">Date</th>
                                            <th scope="">Payment No</th>
                                            <th scope="">Amount ($)</th>
                                            <th scope="">Payment Method</th>
                                            <th scope="">Payment Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($payments as $payment)
                                            <tr>
                                                <th scope="row">{{ $loop->iteration }}</th>
                                                <td>{{ $payment->date }}</td>
                                                <td>{{ $payment->payment_no }}</td>
                                                <td>{{ $payment->amount }}</td>
                                                <td>{{ $payment->payment_method }}</td>
                                                <td>{{ $payment->payment_note }}</td>
                                            </tr> 
                                        @endforeach
                                        
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-5"><h5 class="fs-16 mb-2">IMEI/Serial List:</h5>
                            <table class="table-borderless table-nowrap align-center mb-0 table">
                                <thead>
                                    <tr class="table-active">
                                        <th scope="" style="width: 50px;">#</th>
                                        <th scope="">NAME</th>
                                        <th scope="">IMEI</th>
                                        <th scope="">COLOR</th>
                                        <th scope="">RAM</th>
                                        <th scope="">STORAGE</th>
                                        <th scope="">CONDITION</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($serial_list as $serial)
                                        <tr>
                                            <th scope="row">{{ $loop->iteration }}</th>
                                            <td>{{ $serial->name }}</td>
                                            <td>{{ $serial->imei }}</td>
                                            <td>{{ $serial->color }}</td>
                                            <td>{{ $serial->ram }}</td>
                                            <td>{{ $serial->storage }}</td>
                                            <td>{{ $serial->condition }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    </body>

</html>