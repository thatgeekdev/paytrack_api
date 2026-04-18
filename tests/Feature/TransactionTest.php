<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\Wallet;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_deposit()
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/deposit', [
            'amount' => 100
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'type' => 'deposit',
            'amount' => 100
        ]);
    }

    public function test_user_can_withdraw()
    {
        $user = User::factory()->create();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 200
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/withdraw', [
            'amount' => 100
        ]);

        $response->assertStatus(200);
    }

    public function test_user_cannot_withdraw_more_than_balance()
    {
        $user = User::factory()->create();

        Wallet::create([
            'user_id' => $user->id,
            'balance' => 50
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/withdraw', [
            'amount' => 100
        ]);

        $response->assertStatus(500);
    }

    public function test_user_can_transfer()
    {
        $sender = User::factory()->create();
        $receiver = User::factory()->create();

        Wallet::create(['user_id' => $sender->id, 'balance' => 200]);
        Wallet::create(['user_id' => $receiver->id, 'balance' => 0]);

        $this->actingAs($sender, 'sanctum');

        $response = $this->postJson('/api/transfer', [
            'receiver_id' => $receiver->id,
            'amount' => 100
        ]);

        $response->assertStatus(200);
    }
    
    public function test_user_cannot_transfer_to_self()
    {
        $user = User::factory()->create();

        Wallet::create(['user_id' => $user->id, 'balance' => 200]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/transfer', [
            'receiver_id' => $user->id,
            'amount' => 50
        ]);

        $response->assertStatus(500);
    }
}
