<?php

namespace App\Domain\Customers\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:100',
            'email' => 'nullable|email|max:100|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'cpf' => 'nullable|string|max:14|unique:customers,cpf',
            'rg' => 'nullable|string|max:20|unique:customers,rg',
            'birth_date' => 'nullable|date',
            'address' => 'nullable|string|max:200',
            'number' => 'nullable|string|max:10',
            'complement' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:2',
            'zip_code' => 'nullable|string|max:10',
        ];

        // Se for atualização, ajustar regras para campos únicos
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $customerId = $this->route('id');
            $rules['email'] = "nullable|email|max:100|unique:customers,email,{$customerId},customer_id";
            $rules['cpf'] = "nullable|string|max:14|unique:customers,cpf,{$customerId},customer_id";
            $rules['rg'] = "nullable|string|max:20|unique:customers,rg,{$customerId},customer_id";
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.email' => 'O email deve ser válido',
            'email.unique' => 'Este email já está em uso',
            'cpf.unique' => 'Este CPF já está em uso',
            'rg.unique' => 'Este RG já está em uso',
        ];
    }
}
