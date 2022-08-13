<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseVariation;
use App\Models\ProductTransfer;
use App\Models\User;
use DB;
use Exception;

class ProductLocationController extends Controller
{
    public function onHand()
    {
        if(!auth()->user()->hasPermission("product-location.on-hand"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $products_on_hand = PurchaseVariation::join('users as u', 'u.id', '=', 'purchase_variations.belongs_to')
            ->select(

                'u.id',
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as name'),
                DB::raw('SUM(purchase_variations.quantity_available) as total')

            )->where('purchase_variations.quantity_available', '>', 0)
            ->groupBy('purchase_variations.belongs_to')
            ->orderBy('u.first_name', 'asc')
            ->get();
        
        return response(['products_on_hand' => $products_on_hand], 200);
    }

    public function onHandVariations($user_id)
    {
        if(!auth()->user()->hasPermission("product-location.on-hand"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $on_hand_variations = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),

            )->where('purchase_variations.belongs_to', '=', $user_id)
            ->where('purchase_variations.quantity_available', '>', 0)
            ->orderBy('p.name', 'asc')
            ->get();

        return response(['on_hand_variations' => $on_hand_variations], 200);
    }

    public function transferView()
    {
        if(!auth()->user()->hasPermission("product-location.transfer"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $purchase_variations = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->select(

                'purchase_variations.id',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),
                'purchase_variations.quantity_available'

            )->where('purchase_variations.belongs_to', '=', auth()->user()->id)
            ->where('purchase_variations.quantity_available', '>', 0)
            ->orderBy('p.name', 'asc')
            ->get();

        $receiver = User::select(['id', 'first_name', 'last_name'])->where('type', '=', 1)->where('id', '<>', auth()->user()->id)->orderBy('first_name', 'asc')->get();

        return response([
            'purchase_variations' => $purchase_variations,
            'receiver' => $receiver
        ], 200);
    }

    public function transfer(Request $request)
    {
        if(!auth()->user()->hasPermission("product-location.transfer"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $request->validate([
            'purchase_variation_ids' => 'required | array',
            'receiver_id' => 'required | numeric',
            'sender_pin' => 'required | numeric',
            'receiver_pin' => 'required | numeric'
        ], [
            'purchase_variation_ids.required' => 'Please specify the purchase variations !',
            'purchase_variation_ids.array' => 'Purchase Variation IDs should be in an array !',

            'receiver_id.required' => 'Please select the receiver !',
            'receiver_id.numeric' => 'Receiver ID should be numeric !',

            'sender_pin.required' => 'Please enter PIN Number of the sender!',
            'sender_pin.numeric' => 'PIN Number should be numeric !',

            'receiver_pin.required' => 'Please enter PIN Number of the receiver!',
            'receiver_pin.numeric' => 'PIN Number should be numeric !'
        ]);


        if(auth()->user()->pin_number != $request->sender_pin)
        {
            return response([
                'errors' => [
                    'sender_pin' => ['Invalid Pin Number !']
                ]
            ], 409);
        }


        $receiver = User::find($request->receiver_id);

        if($receiver->pin_number != $request->receiver_pin)
        {
            return response([
                'errors' => [
                    'receiver_pin' => ['Invalid Pin Number !']
                ]
            ], 409);
        }


        $flag = true;

        $batch_no = null;


        DB::beginTransaction();

        try {

            foreach($request->purchase_variation_ids as $purchase_variation_id)
            {
                $purchase_variation = PurchaseVariation::find($purchase_variation_id);

                $purchase_variation->belongs_to = $receiver->id;

                $purchase_variation->save();


                $product_transfer = new ProductTransfer();

                $product_transfer->sender_id = auth()->user()->id;

                $product_transfer->receiver_id = $receiver->id;

                $product_transfer->purchase_variation_id = $purchase_variation_id;

                $product_transfer->save();


                if($flag)
                {
                    $batch_no = $product_transfer->id + 100;

                    $flag = false;
                }


                $product_transfer->batch_no = $batch_no;

                $product_transfer->save();
            }

            
            DB::commit();

            return response(['message' => 'Product Transfer Complete !'], 200);

        } catch(Exception $ex) {

            DB::rollBack();

            return response([
                'message' => 'Internal Server Error !',
                'error' => $ex->getMessage()
            ], 500);

        }
    }

    public function transferHistory()
    {
        if(!auth()->user()->hasPermission("product-location.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }
        
        $transfer_history = ProductTransfer::join('users as u', 'u.id', '=', 'product_transfers.sender_id')
            ->join('users as u2', 'u2.id', '=', 'product_transfers.receiver_id')
            ->select(

                'product_transfers.batch_no',
                DB::raw('DATE_FORMAT(product_transfers.created_at, "%m/%d/%Y %H:%i:%s") as date'),
                DB::raw('CONCAT_WS(" ", u.first_name, u.last_name) as sender'),
                DB::raw('CONCAT_WS(" ", u2.first_name, u2.last_name) as receiver'),
                DB::raw('COUNT(product_transfers.id) as total')

            )->groupBy('product_transfers.batch_no')
            ->orderBy('product_transfers.created_at', 'desc')
            ->get();

        return response(['transfer_history' => $transfer_history], 200);
    }

    public function transferHistoryDetails($batch_no)
    {
        if(!auth()->user()->hasPermission("product-location.history"))
        {
            return response(['message' => 'Permission Denied !'], 403);
        }

        $transfer_history_details = PurchaseVariation::join('products as p', 'p.id', '=', 'purchase_variations.product_id')
            ->join('product_transfers as pt', 'pt.purchase_variation_id', '=', 'purchase_variations.id')
            ->select(

                'purchase_variations.id',
                'p.name',
                'p.sku',
                DB::raw('IF(purchase_variations.serial is null, "N/A", purchase_variations.serial) as imei'),

            )->where('pt.batch_no', '=', $batch_no)
            ->orderBy('p.name', 'asc')
            ->get();

        return response(['transfer_history_details' => $transfer_history_details], 200);
    }


}
