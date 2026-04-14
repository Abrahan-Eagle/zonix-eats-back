<?php

use App\Http\Controllers\Buyer\BuyerProfileController;
use App\Http\Controllers\Buyer\CartController;
use App\Http\Controllers\Buyer\OrderController as BuyerOrderController;
use App\Http\Controllers\Buyer\RestaurantController;
use App\Http\Controllers\Profiles\PhoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('buyer')->middleware(['auth:sanctum', 'role:users'])->group(function () {
    Route::get('/profiles/{profile}', [BuyerProfileController::class, 'show']);
    Route::put('/profiles/{profile}', [BuyerProfileController::class, 'update']);
});

Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('buyer/payments')->middleware(['role:users', 'throttle:30,1'])->group(function () {
        Route::get('/methods', [App\Http\Controllers\Buyer\PaymentController::class, 'getPaymentMethods']);
        Route::post('/card', [App\Http\Controllers\Buyer\PaymentController::class, 'processCardPayment']);
        Route::post('/mobile', [App\Http\Controllers\Buyer\PaymentController::class, 'processMobilePayment']);
        Route::post('/paypal', [App\Http\Controllers\Buyer\PaymentController::class, 'processPayPalPayment']);
        Route::post('/mercadopago', [App\Http\Controllers\Buyer\PaymentController::class, 'processMercadoPagoPayment']);
        Route::post('/cash', [App\Http\Controllers\Buyer\PaymentController::class, 'confirmCashPayment']);
        Route::post('/refund', [App\Http\Controllers\Buyer\PaymentController::class, 'requestRefund']);
        Route::get('/receipt/{orderId}', [App\Http\Controllers\Buyer\PaymentController::class, 'getPaymentReceipt']);
        Route::get('/history', [App\Http\Controllers\Buyer\PaymentController::class, 'getPaymentHistory']);
        Route::get('/statistics', [App\Http\Controllers\Buyer\PaymentController::class, 'getPaymentStatistics']);
    });

    Route::post('/buyer/delivery-fee/calculate', [BuyerOrderController::class, 'calculateDeliveryFee']);

    Route::prefix('buyer/tracking')->middleware('role:users')->group(function () {
        Route::get('/order/{orderId}', [App\Http\Controllers\Buyer\OrderTrackingController::class, 'getOrderStatus']);
        Route::get('/delivery-agent/{orderId}', [App\Http\Controllers\Buyer\OrderTrackingController::class, 'getDeliveryAgentLocation']);
    });

    Route::prefix('buyer/reviews')->middleware('role:users')->group(function () {
        Route::post('/restaurant', [App\Http\Controllers\Buyer\ReviewController::class, 'rateRestaurant']);
        Route::post('/delivery-agent', [App\Http\Controllers\Buyer\ReviewController::class, 'rateDeliveryAgent']);
        Route::get('/restaurant/{commerceId}', [App\Http\Controllers\Buyer\ReviewController::class, 'getRestaurantReviews']);
        Route::get('/delivery-agent/{agentId}', [App\Http\Controllers\Buyer\ReviewController::class, 'getDeliveryAgentReviews']);
        Route::post('/{reviewId}/report', [App\Http\Controllers\Buyer\ReviewController::class, 'reportReview']);
    });

    Route::prefix('buyer/chat')->group(function () {
        Route::get('/messages/{orderId}', [App\Http\Controllers\Buyer\ChatController::class, 'getChatMessages']);
        Route::post('/send', [App\Http\Controllers\Buyer\ChatController::class, 'sendMessage']);
        Route::post('/mark-read', [App\Http\Controllers\Buyer\ChatController::class, 'markAsRead']);
        Route::get('/unread/{orderId}', [App\Http\Controllers\Buyer\ChatController::class, 'getUnreadMessages']);
    });

    Route::prefix('buyer/search')->middleware('role:users')->group(function () {
        Route::get('/restaurants', [App\Http\Controllers\Buyer\SearchController::class, 'searchRestaurants']);
        Route::get('/products', [App\Http\Controllers\Buyer\SearchController::class, 'searchProducts']);
        Route::get('/categories', [App\Http\Controllers\Buyer\SearchController::class, 'getCategories']);
        Route::get('/business-types', function () {
            return response()->json([
                'success' => true,
                'data' => \App\Models\BusinessType::select('id', 'name', 'icon', 'description')->orderBy('name')->get(),
            ]);
        });
        Route::get('/suggestions', [App\Http\Controllers\Buyer\SearchController::class, 'getSearchSuggestions']);
    });

    Route::prefix('buyer/promotions')->group(function () {
        Route::get('/active', [App\Http\Controllers\Buyer\PromotionController::class, 'getActivePromotions']);
        Route::get('/coupons', [App\Http\Controllers\Buyer\PromotionController::class, 'getAvailableCoupons']);
        Route::post('/validate-coupon', [App\Http\Controllers\Buyer\PromotionController::class, 'validateCoupon']);
        Route::post('/apply-coupon', [App\Http\Controllers\Buyer\PromotionController::class, 'applyCouponToOrder']);
        Route::get('/coupon-history', [App\Http\Controllers\Buyer\PromotionController::class, 'getCouponHistory']);
    });

    Route::prefix('buyer/addresses')->group(function () {
        Route::get('/', [App\Http\Controllers\Buyer\AddressController::class, 'getUserAddresses']);
        Route::post('/', [App\Http\Controllers\Buyer\AddressController::class, 'createAddress']);
        Route::put('/{addressId}', [App\Http\Controllers\Buyer\AddressController::class, 'updateAddress']);
        Route::delete('/{addressId}', [App\Http\Controllers\Buyer\AddressController::class, 'deleteAddress']);
        Route::post('/{addressId}/default', [App\Http\Controllers\Buyer\AddressController::class, 'setDefaultAddress']);
        Route::get('/default', [App\Http\Controllers\Buyer\AddressController::class, 'getDefaultAddress']);
    });

    Route::prefix('phones')->group(function () {
        Route::get('/', [PhoneController::class, 'index']);
        Route::get('/operator-codes', [PhoneController::class, 'getOperatorCodes']);
        Route::get('/by-user/{userId}', [PhoneController::class, 'phonesByUserId']);
        Route::post('/', [PhoneController::class, 'store']);
        Route::get('/{id}', [PhoneController::class, 'show']);
        Route::put('/{id}', [PhoneController::class, 'update']);
        Route::delete('/{id}', [PhoneController::class, 'destroy']);
    });

    Route::prefix('buyer/gamification')->group(function () {
        Route::get('/points', [App\Http\Controllers\Buyer\GamificationController::class, 'getUserPoints']);
        Route::get('/rewards', [App\Http\Controllers\Buyer\GamificationController::class, 'getAvailableRewards']);
        Route::post('/redeem', [App\Http\Controllers\Buyer\GamificationController::class, 'redeemReward']);
        Route::get('/badges', [App\Http\Controllers\Buyer\GamificationController::class, 'getUserBadges']);
        Route::get('/leaderboard', [App\Http\Controllers\Buyer\GamificationController::class, 'getLeaderboard']);
        Route::get('/stats', [App\Http\Controllers\Buyer\GamificationController::class, 'getGamificationStats']);
    });

    Route::prefix('buyer/loyalty')->group(function () {
        Route::get('/info', [App\Http\Controllers\Buyer\LoyaltyController::class, 'getLoyaltyInfo']);
        Route::get('/volume-discounts', [App\Http\Controllers\Buyer\LoyaltyController::class, 'getVolumeDiscounts']);
        Route::get('/referral-code', [App\Http\Controllers\Buyer\LoyaltyController::class, 'generateReferralCode']);
        Route::post('/apply-referral', [App\Http\Controllers\Buyer\LoyaltyController::class, 'applyReferralCode']);
        Route::get('/benefits-history', [App\Http\Controllers\Buyer\LoyaltyController::class, 'getBenefitsHistory']);
        Route::get('/stats', [App\Http\Controllers\Buyer\LoyaltyController::class, 'getLoyaltyStats']);
        Route::get('/upcoming-benefits', [App\Http\Controllers\Buyer\LoyaltyController::class, 'getUpcomingBenefits']);
    });

    Route::prefix('user')->group(function () {
        Route::get('/activity-history', [App\Http\Controllers\Buyer\ActivityController::class, 'getUserActivityHistory']);
        Route::get('/activity-stats', [App\Http\Controllers\Buyer\ActivityController::class, 'getActivityStats']);

        Route::post('/export-data', [App\Http\Controllers\Buyer\ExportController::class, 'requestDataExport']);
        Route::get('/export-status/{exportId}', [App\Http\Controllers\Buyer\ExportController::class, 'getExportStatus']);
        Route::get('/download-export/{exportId}', [App\Http\Controllers\Buyer\ExportController::class, 'downloadExport']);
        Route::get('/export-history', [App\Http\Controllers\Buyer\ExportController::class, 'getExportHistory']);

        Route::get('/privacy-settings', [App\Http\Controllers\Buyer\PrivacyController::class, 'getPrivacySettings']);
        Route::put('/privacy-settings', [App\Http\Controllers\Buyer\PrivacyController::class, 'updatePrivacySettings']);
        Route::get('/privacy-policy', [App\Http\Controllers\Buyer\PrivacyController::class, 'getPrivacyPolicy']);
        Route::get('/terms-of-service', [App\Http\Controllers\Buyer\PrivacyController::class, 'getTermsOfService']);

        Route::post('/request-deletion', [App\Http\Controllers\Buyer\AccountDeletionController::class, 'requestAccountDeletion']);
        Route::post('/confirm-deletion', [App\Http\Controllers\Buyer\AccountDeletionController::class, 'confirmAccountDeletion']);
        Route::delete('/cancel-deletion', [App\Http\Controllers\Buyer\AccountDeletionController::class, 'cancelDeletionRequest']);
        Route::get('/deletion-status', [App\Http\Controllers\Buyer\AccountDeletionController::class, 'getDeletionStatus']);
    });
});

Route::middleware(['auth:sanctum', 'role:users'])->prefix('buyer')->group(function () {
    Route::get('/restaurants', [RestaurantController::class, 'index']);
    Route::get('/restaurants/{id}', [RestaurantController::class, 'show']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::get('/cart', [CartController::class, 'show']);
    Route::delete('/cart', [CartController::class, 'clear']);
    Route::put('/cart/update-quantity', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/{productId}', [CartController::class, 'remove']);
    Route::post('/cart/notes', [CartController::class, 'addNotes']);
    Route::post('/orders', [BuyerOrderController::class, 'store'])->middleware('throttle:create');
    Route::get('/orders', [BuyerOrderController::class, 'index']);
    Route::get('/orders/{id}', [BuyerOrderController::class, 'show']);
    Route::get('/products/{id}', [\App\Http\Controllers\Buyer\ProductController::class, 'show']);
    Route::get('/products', [\App\Http\Controllers\Buyer\ProductController::class, 'index']);
    Route::post('/orders/{id}/comprobante', [\App\Http\Controllers\Buyer\OrderController::class, 'uploadComprobante']);

    Route::get('/orders/{id}/available-payment-methods', [BuyerOrderController::class, 'getAvailablePaymentMethodsForOrder']);
    Route::get('/orders/{id}/payment-info', [BuyerOrderController::class, 'getPaymentInfo']);
    Route::post('/orders/{id}/payment-proof', [BuyerOrderController::class, 'uploadPaymentProof']);
    Route::post('/orders/{id}/cancel', [BuyerOrderController::class, 'cancelOrder']);
    Route::get('/orders/{id}/delivery-qr', [BuyerOrderController::class, 'deliveryQr']);

    Route::get('/posts', [\App\Http\Controllers\Buyer\PostController::class, 'index']);
    Route::get('/posts/{id}', [\App\Http\Controllers\Buyer\PostController::class, 'show']);
    Route::post('/posts/{id}/favorite', [\App\Http\Controllers\Buyer\PostController::class, 'toggleFavorite']);
    Route::get('/favorites', [\App\Http\Controllers\Buyer\PostController::class, 'favorites']);

    Route::get('/orders/{orderId}/tracking', [\App\Http\Controllers\Buyer\TrackingController::class, 'getOrderTracking']);
    // updateDeliveryLocation removido: solo el delivery agent puede actualizar GPS via POST /api/delivery/location/update

    Route::get('/orders/{orderId}/messages', [\App\Http\Controllers\Chat\ChatController::class, 'getMessages']);
    Route::post('/orders/{orderId}/messages', [\App\Http\Controllers\Chat\ChatController::class, 'sendMessage']);

    Route::post('/reviews', [\App\Http\Controllers\ReviewController::class, 'store']);
    Route::get('/reviews/{reviewableId}/{reviewableType}', [\App\Http\Controllers\ReviewController::class, 'index']);
    Route::put('/reviews/{reviewId}', [\App\Http\Controllers\ReviewController::class, 'update']);
    Route::delete('/reviews/{reviewId}', [\App\Http\Controllers\ReviewController::class, 'destroy']);
    Route::get('/reviews/{reviewableId}/{reviewableType}/can-review', [\App\Http\Controllers\ReviewController::class, 'canReview']);

    Route::get('/disputes', [\App\Http\Controllers\Buyer\DisputeController::class, 'index']);
    Route::post('/disputes', [\App\Http\Controllers\Buyer\DisputeController::class, 'store']);
    Route::get('/disputes/{id}', [\App\Http\Controllers\Buyer\DisputeController::class, 'show']);

    Route::get('/export', [\App\Http\Controllers\Buyer\ExportController::class, 'export']);
    Route::delete('/account', [\App\Http\Controllers\Buyer\AccountDeletionController::class, 'deleteAccount']);
});
