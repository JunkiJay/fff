<?php

declare(strict_types=1);

namespace Feature\Http\Admin;

use Tests\TestCase;

/**
 * @covers \App\Http\Controllers\WithdrawController
 */
class WithdrawControllerTest extends TestCase
{
    public function testInit()
    {
        $user = $this->newUser();

        $this->actingAs($user);

        $response = $this->post(route('withdraw.init'));
        $response->assertOk();

        $this->assertEquals(
            [
                'data' => [],
                'unq' => [],
            ],
            $response->json()
        );
    }
}