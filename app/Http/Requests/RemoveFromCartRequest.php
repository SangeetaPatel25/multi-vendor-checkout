<?php

namespace App\Http\Requests;

use App\Models\CartItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RemoveFromCartRequest extends FormRequest
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
        ];
    }
}
