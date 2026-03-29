<?php

namespace App\Http\Requests;

use App\Models\CartItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if (!auth()->check()) {
            return false;
        }

        // Check if the cart item belongs to the authenticated user
        $cartItem = CartItem::where('id', $this->route('cart_item_id') ?? $this->input('cart_item_id'))
                           ->whereHas('cart', function ($query) {
                               $query->where('user_id', auth()->id());
                           })
                           ->first();

        return $cartItem !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cart_item_id' => 'required|exists:cart_items,id',
            'quantity' => 'required|integer|min:1',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'cart_item_id.required' => 'Cart item ID is required',
            'cart_item_id.exists' => 'Cart item does not exist',
            'quantity.required' => 'Quantity is required',
            'quantity.integer' => 'Quantity must be a number',
            'quantity.min' => 'Quantity must be at least 1',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('cart_item_id')) {
                $cartItem = CartItem::with('product')
                                   ->where('id', $this->input('cart_item_id'))
                                   ->whereHas('cart', function ($query) {
                                       $query->where('user_id', auth()->id());
                                   })
                                   ->first();

                if ($cartItem && $this->input('quantity') > $cartItem->product->stock) {
                    $validator->errors()->add('quantity', 'Requested quantity exceeds available stock. Available: ' . $cartItem->product->stock);
                }
            }
        });
    }
}
