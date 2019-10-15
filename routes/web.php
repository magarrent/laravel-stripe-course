<?php

use App\User;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/setup-card', function(Request $request){
    $user = User::find(auth()->user()->id);

    return view('update-payment-method', [
        'intent' => $user->createSetupIntent()
    ]);
});

Route::post('/card-save', function (Request $request) {
    $user = User::find(auth()->user()->id);

    $user->updateDefaultPaymentMethod($request->get('card'));
});

Route::get('/{sku}/product-buy', function(Request $request, $sku) {
    $user = User::find(auth()->user()->id);

    \Stripe\Stripe::setApiKey('sk_test_exHMG4wpVMk1UmqLRwjdOc0z00J7Fdz6xE');

    $sku = \Stripe\SKU::retrieve($sku);

    $user->invoiceFor($sku->attributes->name, $sku->price, [
    ], [
        'tax_percent' => 21,
    ]);
})->name('product-buy');


Route::get('/{plan}/plan-buy', function (Request $request, $plan) {
    $user = User::find(auth()->user()->id);

    \Stripe\Stripe::setApiKey('sk_test_exHMG4wpVMk1UmqLRwjdOc0z00J7Fdz6xE');

    $plan = \Stripe\Plan::retrieve($plan);

    $user->newSubscription($plan->product, $plan->id)->create($user->defaultPaymentMethod()->asStripePaymentMethod()->id);

})->name('plan-buy');
