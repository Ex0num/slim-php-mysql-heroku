<?php

require_once './middlewares/Saver.php';
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

class Logger
{

    public static function Login(Request $request, RequestHandler $handler) : ResponseMW
    {
        //---------------------------------------- RECIBO LA DATA DEL TOKEN ----------------------------------------//

        //Recibimos el token por "Authorization".
        $header = $request->getHeaderLine('Authorization');

        //----------------------------------------------------------------------------------------------------------//

        //Creo una respuesta
        $response = new ResponseMW();

        if ($header != null)
        {
            //Le saco "Bearer"
            $token = trim(explode("Bearer", $header)[1]);

            //En un principio el token no es valido. Si logra pasar la verificacion sin excepciones, pasara a ser valido
            $esValido = false;

            try 
            {
                //Llama a la funcion verificadora del token para INTENTAR verificar. 
                //Si adentro del proceso de validacion no hubo errores, entonces es valido.
                AutentificadorJWT::verificarToken($token);
                $esValido = true;

                //Llama a la funcion verificadora de la data del token.
                $esUsuarioExistente = AutentificadorJWT::VerificarUsuario($token);

                //Solo si es un token valido, y el usuario (que viene dentro de ese token) existe en la db.
                if ($esValido == true && $esUsuarioExistente == true) 
                {
                    //Voy a ir a la accion que se desea realizar.
                    $response = $handler->handle($request);

                    //Me guardo a mi accion realizada en la db.
                    Saver::GuardarAccion($response);

                    //De la response devuelta me hago el decode del body. Ahi tengo acceso a toda la info de la accion realizada.
                    $resultadoAccion = json_decode($response->getBody());

                    return $response->withHeader('Content-Type', 'application/json');
                }
                else
                {
                    $payload = json_encode(array("mensajeFinal" => "El usuario no es valido.",
                    "exito" => "fallido","tipo" => "login","hora" => date('h:i:s'),"idUsuarioResponsable" => null, 
                    "idUsuario" => null,"idProducto" => null, "idMesa" => null, 
                    "idPedido" => null,"idVenta" => null)); 

                    $response->getBody()->write($payload);
                    Saver::GuardarAccion($response);
                    return $response->withHeader('Content-Type', 'application/json');
                }
            } 
            catch (Exception $e) 
            {
                $payload = json_encode(array("mensajeFinal" => "Hubo un error en la verificacion del token.",
                "exito" => "fallido","tipo" => "login","hora" => date('h:i:s'),"idUsuarioResponsable" => null, 
                "idUsuario" => null,"idProducto" => null, "idMesa" => null, 
                "idPedido" => null,"idVenta" => null)); 

                $response->getBody()->write($payload);
                Saver::GuardarAccion($response);
                return $response->withHeader('Content-Type', 'application/json');
            }
        }
        else
        {
            $payload = json_encode(array("mensajeFinal" => "No se recibio token alguno.",
            "exito" => "fallido","tipo" => "login","hora" => date('h:i:s'),"idUsuarioResponsable" => null, 
            "idUsuario" => null,"idProducto" => null, "idMesa" => null, 
            "idPedido" => null,"idVenta" => null));

            $response->getBody()->write($payload);
            Saver::GuardarAccion($response);
            return $response->withHeader('Content-Type', 'application/json');
        }
    }


}