<?php

namespace App\Http\Controllers;

use App\Models\AffiliateLink;
use App\Models\Conversion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ConversionController extends Controller
{
    /**
     * Tạo conversion từ webhook/callback
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tracking_code' => 'required|string',
            'order_id' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ], [
            'tracking_code.required' => 'Tracking code là bắt buộc',
            'order_id.required' => 'Order ID là bắt buộc',
            'amount.required' => 'Số tiền là bắt buộc',
            'amount.numeric' => 'Số tiền phải là số',
            'amount.min' => 'Số tiền phải lớn hơn 0',
            'commission_rate.numeric' => 'Tỷ lệ hoa hồng phải là số',
            'commission_rate.min' => 'Tỷ lệ hoa hồng phải lớn hơn 0',
            'commission_rate.max' => 'Tỷ lệ hoa hồng không được vượt quá 100%',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Dữ liệu không hợp lệ',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Tìm affiliate link
            $affiliateLink = AffiliateLink::where('tracking_code', $request->tracking_code)
                ->where('status', 'active')
                ->with(['publisher', 'product', 'campaign'])
                ->first();

            if (!$affiliateLink) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tracking code không tồn tại hoặc đã bị vô hiệu hóa'
                ], 404);
            }

            // Tính hoa hồng
            $commissionRate = $request->commission_rate ?? $affiliateLink->getEffectiveCommissionRateAttribute();
            $commission = ($request->amount * $commissionRate) / 100;

            // Tạo conversion record
            $conversion = Conversion::create([
                'affiliate_link_id' => $affiliateLink->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'product_id' => $affiliateLink->product_id,
                'shop_id' => $affiliateLink->product->user_id ?? null,
                'tracking_code' => $request->tracking_code,
                'order_id' => $request->order_id,
                'amount' => $request->amount,
                'commission' => $commission,
                'converted_at' => now(),
                'status' => 'pending',
                'is_commission_processed' => false,
            ]);

            DB::commit();

            Log::info('Conversion created successfully', [
                'conversion_id' => $conversion->id,
                'publisher_id' => $affiliateLink->publisher_id,
                'amount' => $request->amount,
                'commission' => $commission
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conversion đã được tạo thành công',
                'data' => [
                    'conversion_id' => $conversion->id,
                    'publisher_id' => $affiliateLink->publisher_id,
                    'amount' => $request->amount,
                    'commission' => $commission,
                    'commission_rate' => $commissionRate,
                    'status' => $conversion->status
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error creating conversion', [
                'tracking_code' => $request->tracking_code,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi tạo conversion: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lấy danh sách conversions của publisher
     */
    public function getPublisherConversions(Request $request)
    {
        $publisher = $request->user();
        
        $conversions = Conversion::where('publisher_id', $publisher->id)
            ->with(['affiliateLink', 'product'])
            ->orderBy('converted_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $conversions
        ]);
    }

    /**
     * Lấy thống kê conversions của publisher
     */
    public function getPublisherStats(Request $request)
    {
        $publisher = $request->user();
        
        $stats = [
            'total_conversions' => $publisher->conversions()->count(),
            'total_amount' => $publisher->conversions()->sum('amount'),
            'total_commission' => $publisher->conversions()->sum('commission'),
            'average_order_value' => $publisher->conversions()->avg('amount'),
            'conversion_rate' => $publisher->getConversionRateAttribute(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
