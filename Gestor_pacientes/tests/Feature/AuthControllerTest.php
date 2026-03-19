<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\AuthController;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected AuthController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new AuthController();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function mockRequest(array $data): Request
    {
        $request = Request::create('/api/login', 'POST', $data);
        $request->setJson(new \Symfony\Component\HttpFoundation\InputBag($data));
        return $request;
    }

    // -------------------------------------------------------------------------
    // login()
    // -------------------------------------------------------------------------

    public function test_login_retorna_200_con_credenciales_validas(): void
    {
        Auth::shouldReceive('attempt')->once()->andReturn('fake_token');
        Auth::shouldReceive('factory->getTTL')->once()->andReturn(60);

        $response = $this->controller->login($this->mockRequest([
            'email'    => 'admin@example.com',
            'password' => 'password123',
        ]));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_login_retorna_token_con_credenciales_validas(): void
    {
        Auth::shouldReceive('attempt')->once()->andReturn('fake_token');
        Auth::shouldReceive('factory->getTTL')->once()->andReturn(60);

        $data = $this->controller->login($this->mockRequest([
            'email'    => 'admin@example.com',
            'password' => 'password123',
        ]))->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('fake_token', $data['access_token']);
        $this->assertEquals('bearer', $data['token_type']);
        $this->assertArrayHasKey('expires_in', $data);
    }

    public function test_login_retorna_401_con_credenciales_invalidas(): void
    {
        Auth::shouldReceive('attempt')->once()->andReturn(false);

        $response = $this->controller->login($this->mockRequest([
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]));

        $this->assertEquals(401, $response->getStatusCode());
    }

    public function test_login_retorna_success_false_con_credenciales_invalidas(): void
    {
        Auth::shouldReceive('attempt')->once()->andReturn(false);

        $data = $this->controller->login($this->mockRequest([
            'email'    => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]))->getData(true);

        $this->assertFalse($data['success']);
        $this->assertEquals('Credenciales incorrectas.', $data['message']);
    }

    // -------------------------------------------------------------------------
    // logout()
    // -------------------------------------------------------------------------

    public function test_logout_retorna_200(): void
    {
        Auth::shouldReceive('logout')->once();

        $response = $this->controller->logout();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_logout_retorna_success_true_y_mensaje(): void
    {
        Auth::shouldReceive('logout')->once();

        $data = $this->controller->logout()->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('Sesión cerrada correctamente.', $data['message']);
    }

    // -------------------------------------------------------------------------
    // me()
    // -------------------------------------------------------------------------

    public function test_me_retorna_200(): void
    {
        Auth::shouldReceive('user')->once()->andReturn((object) ['id' => 1, 'email' => 'admin@example.com']);

        $response = $this->controller->me();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_me_retorna_datos_del_usuario_autenticado(): void
    {
        $user = (object) ['id' => 1, 'email' => 'admin@example.com'];
        Auth::shouldReceive('user')->once()->andReturn($user);

        $data = $this->controller->me()->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('admin@example.com', $data['data']['email']);
    }

    // -------------------------------------------------------------------------
    // refresh()
    // -------------------------------------------------------------------------

    public function test_refresh_retorna_200(): void
    {
        Auth::shouldReceive('refresh')->once()->andReturn('new_fake_token');
        Auth::shouldReceive('factory->getTTL')->once()->andReturn(60);

        $response = $this->controller->refresh();

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_refresh_retorna_nuevo_token(): void
    {
        Auth::shouldReceive('refresh')->once()->andReturn('new_fake_token');
        Auth::shouldReceive('factory->getTTL')->once()->andReturn(60);

        $data = $this->controller->refresh()->getData(true);

        $this->assertTrue($data['success']);
        $this->assertEquals('new_fake_token', $data['access_token']);
        $this->assertEquals('bearer', $data['token_type']);
    }
}