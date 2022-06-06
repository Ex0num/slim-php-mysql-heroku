<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
// require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// ------------------------// U S U A R I O S // ------------------
$app->group('/usuarios', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{usuario}', \UsuarioController::class . ':TraerUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno');
});

// ------------------------// M E S A S // ------------------
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');
    $group->post('[/]', \MesaController::class . ':CargarUno');
});

// ------------------------// P R O D U C T O S // ------------------
$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');
    $group->post('[/]', \ProductoController::class . ':CargarUno');
});

// ------------------------// P E D I D O S // ------------------
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');  
    $group->post('[/]', \PedidoController::class . ':CargarUno');
});

//----------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response) 
{    
    $response->getBody()->write("Slim Framework 4 PHP Gabriel :D");
    return $response;
});

$app->run();
