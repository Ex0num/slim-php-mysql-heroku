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

require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Logger.php';

require_once './controllers/UsuarioController.php';
require_once './controllers/MesaController.php';
require_once './controllers/PedidoController.php';
require_once './controllers/ProductoController.php';
require_once './controllers/CSV_Controller.php';

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

// ------------------------// U S U A R I O S // ------------------------------------------------------
$app->group('/usuarios', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \UsuarioController::class . ':TraerTodos')->add(\Logger::class . ':Login');

    $group->get('/{id}', \UsuarioController::class . ':TraerUno')->add(\Logger::class . ':Login');
    
    $group->post('[/]', \UsuarioController::class . ':CargarUno');

    $group->put('/{id}', \UsuarioController::class . ':ModificarUno')->add(\Logger::class . ':Login');

    $group->delete('/{id}', \UsuarioController::class . ':BorrarUno')->add(\Logger::class . ':Login');
});

// ------------------------// M E S A S // ------------------------------------------------------------
$app->group('/mesas', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \MesaController::class . ':TraerTodos');

    $group->get('/{id}', \MesaController::class . ':TraerUno');

    $group->post('[/]', \MesaController::class . ':CargarUno');

    $group->put('/{id}', \MesaController::class . ':ModificarUno');

    $group->delete('/{id}', \MesaController::class . ':BorrarUno');

})->add(\Logger::class . ':Login');

// ------------------------// P R O D U C T O S // ---------------------------------------------------
$app->group('/productos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \ProductoController::class . ':TraerTodos');

    $group->get('/{id}', \ProductoController::class . ':TraerUno');

    $group->post('[/]', \ProductoController::class . ':CargarUno');

    $group->put('/{id}', \ProductoController::class . ':ModificarUno');

    $group->delete('/{id}', \ProductoController::class . ':BorrarUno');

})->add(\Logger::class . ':Login');

// ------------------------// P E D I D O S // --------------------------------------------------------
$app->group('/pedidos', function (RouteCollectorProxy $group) 
{
    $group->get('[/]', \PedidoController::class . ':TraerTodos');  
    
    $group->get('/{id}', \PedidoController::class . ':TraerUno');

    $group->post('[/]', \PedidoController::class . ':CargarUno');
    
    $group->put('/{id}', \PedidoController::class . ':ModificarUno');

    $group->delete('/{id}', \PedidoController::class . ':BorrarUno');

    // ------ AÑADIR UNA FOTO A UN PEDIDO ------//
    $group->post('/foto', \PedidoController::class . ':AgregarFoto');

})->add(\Logger::class . ':Login');

// ------------------------// A R C H I V O - C S V // ------------------------------------------------
$app->group('/appFiles', function (RouteCollectorProxy $group) 
{ 
    $group->post('/productos/leer', \CSV_Controller::class . ':DescargarProductosCSV');
    $group->post('/productos/escribir', \CSV_Controller::class . ':CargarProductosCSV');

    $group->get('/usuarios', \CSV_Controller::class . ':DescargarUsuariosCSV');
    $group->get('/pedidos', \CSV_Controller::class . ':CargarPedidosCSV');
});

// ------------------------------// JWT // -------------------------------------------------------------
$app->group('/jwt', function (RouteCollectorProxy $group) 
{
    $group->post('/crearToken', function (Request $request, Response $response) 
    {    
        //Recibo del POSTMAN los datos
        $parametros = $request->getParsedBody();
    
        $user = $parametros['user'];
        $clave = $parametros['clave'];

        $estaEnDB = true; //FUNCION QUE VALIDE SI ESTA EN DB

        if ($estaEnDB == true)
        {
            //Preparamos los datos para crear el token (mediante un array asociativo)
            $datos = array('user' => $user, 'clave' => $clave);
    
            //Luego de crear el token, asigno al payload el token como objeto formateado a .json
            $token = AutentificadorJWT::CrearToken($datos);
            $payload = json_encode(array('jwt' => $token));
        
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
    });

    $group->get('/verificarToken', function (Request $request, Response $response) 
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        //Le saco "Bearer"
        $token = trim(explode("Bearer", $header)[1]);
        
        $esValido = false;
    
        try 
        {
            //Llama a la funcion verificadora del token para INTENTAR verificar.
            AutentificadorJWT::verificarToken($token);

            //Si adentro del proceso de validacion no hubo errores, entonces es valido.
            $esValido = true;
        } 
        catch (Exception $e) 
        {
            $payload = json_encode(array('Token invalido' => $e->getMessage()));
        }
    
        //Aviso que es valido.
        if ($esValido == true) 
        {
          $payload = json_encode(array('Token valido' => $esValido));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
  
    $group->get('/devolverPayLoad', function (Request $request, Response $response) 
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        //Le saco "Bearer"
        $token = trim(explode("Bearer", $header)[1]);

        try 
        {
            //Llama a la funcion que INTENTA obtener el Payload del Token.
            $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
        } 
        catch (Exception $e) 
        {
            $payload = json_encode(array('error' => $e->getMessage()));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
  
    $group->get('/devolverDatos', function (Request $request, Response $response) 
    {
        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        //Le saco "Bearer"
        $token = trim(explode("Bearer", $header)[1]);

        try 
        {
            //Llamo a mi funcion que INTENTA Obtener la data del token. Solo la data.
            $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
        } 
        catch (Exception $e)
        {
            $payload = json_encode(array('error' => $e->getMessage()));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
});

//------------------------------------------------------------------------------------------------------
$app->get('[/]', function (Request $request, Response $response) 
{    
    $response->getBody()->write("Slim Framework 4 PHP Gabriel :D");
    return $response;
});

$app->run();

//------------------------------------------------------------------------------------------------------

?>
