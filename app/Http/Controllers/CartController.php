<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartRequest;
use App\Models\CartItem;
use App\Models\ProductSku;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * 查看购物车
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $cartItems = $request->user()->cartItems()->with(['productSku.product'])->get();
        return view('cart.index', ['cartItems' => $cartItems]);
    }

    /**
     * 加入购物车
     *
     * @param CartRequest $request
     * @return array
     */
    public function add(CartRequest $request)
    {
        $user = $request->user();
        $sku_id = $request->input('sku_id');
        $amount = $request->input('amount');

        // 从数据库中查询该商品是否已经存在购物车中
        if ($cart = $user->cartItems()->where('product_sku_id', $sku_id)->first()) {

            // 如果存在直接叠加商品数量
            $cart->update([
                'amount' => $cart->amount + $amount,
            ]);
        }else{

            // 否则创建一个新的购物车记录
            $cart = new CartItem(['amount' => $amount]);
            $cart->user()->associate($user);
            $cart->productSku()->associate($sku_id);
            $cart->save();
        }

        return [];
    }

    /**
     * 移除购物车商品
     *
     * @param ProductSku $sku
     * @param Request $request
     * @return array
     */
    public function remove(ProductSku $sku, Request $request)
    {
        $request->user()->cartItems()->where('product_sk_id', $sku->id)->delete();
        return [];
    }
}