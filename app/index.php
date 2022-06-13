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

use Illuminate\Database\Capsule\Manager as Capsule;

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

$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

$cointainer = $app->getContainer();

$capsule = new Capsule();

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $_ENV['MYSQL_HOST'],
    'database'  => $_ENV['MYSQL_DB'],
    'username'  => $_ENV['MYSQL_USER'],
    'password'  => $_ENV['MYSQL_PASS'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();;

// ------------------------// U S U A R I O S // ------------------------
$app->group('/usuarios', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');

    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    
    $group->post('[/]', \UsuarioController::class . ':CargarUno');

    $group->put('/{id}', \UsuarioController::class. ':ModificarUno');

    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno');
});

// ------------------------// M E S A S // ------------------------------
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');

    $group->get('/{id}', \MesaController::class . ':TraerUno');

    $group->post('[/]', \MesaController::class . ':CargarUno');

    $group->put('/{id}', \MesaController::class. ':ModificarUno');

    $group->delete('/{id}', \MesaController::class . ':BorrarUno');
});

// ------------------------// P R O D U C T O S // ----------------------
$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');

    $group->get('/{id}', \ProductoController::class . ':TraerUno');

    $group->post('[/]', \ProductoController::class . ':CargarUno');

    $group->put('/{id}', \ProductoController::class. ':ModificarUno');

    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');
});

// ------------------------// P E D I D O S // --------------------------
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');  
    
    $group->get('/{id}', \PedidoController::class . ':TraerUno');

    $group->post('[/]', \PedidoController::class . ':CargarUno');
    
    $group->put('/{id}', \PedidoController::class. ':ModificarUno');

    $group->delete('/{id}', \PedidoController::class . ':BorrarUno');
});

//-----------------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response) 
{    
    $response->getBody()->write("Slim Framework 4 PHP Gabriel :D");
    return $response;
});

$app->run();



?>
