<?php

require_once './models/Producto.php';
use \App\Models\Producto as Producto;

class FilesManagement
{
    public static function EscribirProductosCSV (string $path, $arrayObjetos)
    {
        $escrituraSalioBien = true;

        if ($path != null && $arrayObjetos != null)
        {
            //Abro el archivo (Modo escritura desde 0) ("w") (Me guardo la direc. de mem. del archivo)
            $archivo;
            $archivo = fopen($path,"w");

            //Mi variable string que seria como un "Stringbuilder de C#"
            $string;

            //Por cada objeto que tenga en mi lista
            foreach ($arrayObjetos as $objeto) 
            {
                //Voy a concatenar todas mis propiedades del objeto a las ',' y finalmente a un '\n' expresado como PHP.EOL
                
                if (is_null($objeto) == false)
                {
                    //====================== ESTRUCTURA ORDENADA DEL OBJETO EN EL ARCHIVO CSV =================================================
                    //El orden de los campos en coma.
                    //nombre - precio - tiempoMinutos - area - tipo - stock
                    //=========================================================================================================================

                    //======== IMPORTANTE CAMBIAR ESTA LINEA EN CUESTION DEL OBJETO QUE SE USE =========== (PHP.EOL persiste.)
                    $string = $objeto->nombre . "," . $objeto->precio . "," . 
                    $objeto->tiempoMinutos . "," . $objeto->area . "," . 
                    $objeto->tipo . ",". $objeto->stock . "." . 
                    $objeto->fechaAlta . "," . $objeto->fechaModificacion . "," .
                    $objeto->fechaBaja .PHP_EOL;
                    //======== IMPORTANTE CAMBIAR ESTA LINEA EN CUESTION DEL OBJETO QUE SE USE =========== (PHP.EOL persiste.)

                    //Escribo finalmente en el archivo el string que obtuve, listo para escribir en la proxima linea el proximo objeto.
                    fwrite($archivo,$string);
                }
                else
                {
                    $escrituraSalioBien = false;
                    return $escrituraSalioBien;          
                }
            }

            //Finalmente, cierro el archivo para que se guarden los cambios.
            fclose($archivo);
        }
        else
        {
            $escrituraSalioBien = false;
        }
        
        return $escrituraSalioBien;
    }

    public static function LeerProductosCSV (string $path)
    {
        $lecturaSalioBien = false;

        //Creo mi array auxiliar de objetos.
        $_listaAuxiliarObjetos = array();

        //Abro el archivo (Modo lectura) ("r") (Me guardo la direc. de mem. del archivo)
        $archivo;
        $archivo = fopen($path,"r");

        //Guardo el tamanio de mi archivo (Despues lo voy a precisar para el fgets)
        $archivoLength = filesize($path);

        $stringLineaLeida;
        $arraySeparaditoEnValores;
        $objetoAuxiliar;

        $i = 0;
    
        //Mientras que el puntero de lectura del archivo no haya llegado al final
        while(feof($archivo) == false)
        {
  
            if ($archivoLength < 2)
            {
                break;
            }

            //Leo una linea entera, y la guardo
            $stringLineaLeida = fgets($archivo,$archivoLength);

            //Si el string de la linea leida es mayor a 1 caracter, significa que lei un nuevo objeto valido
            if (strlen($stringLineaLeida) > 1)
            {
                //Me guardo en un array las posiciones 0,1,2 respectivamente (de los atributos), los valores ya separados en coma (sin la coma)
                $arraySeparaditoEnValores = explode(',', $stringLineaLeida);
                
                //====================== ESTRUCTURA ORDENADA DEL OBJETO EN EL ARCHIVO CSV =================================================
                //El orden de los campos en coma.
                //nombre - precio - tiempoMinutos - area - tipo - stock
                //=========================================================================================================================

                //Creo un objeto nuevo y los instancio con los valores leidos

                //=============== IMPORTANTE CAMBIAR ESTAS LINEAS EN CUESTION DEL OBJETO QUE SE USE =======================================
                $objetoAuxiliar = new Producto();

                $objetoAuxiliar->nombre = $arraySeparaditoEnValores[0];
                $objetoAuxiliar->precio = $arraySeparaditoEnValores[1];
                $objetoAuxiliar->tiempoMinutos = $arraySeparaditoEnValores[2];
                $objetoAuxiliar->area = $arraySeparaditoEnValores[3];
                $objetoAuxiliar->tipo = $arraySeparaditoEnValores[4];
                $objetoAuxiliar->stock = $arraySeparaditoEnValores[5];

                //=============== IMPORTANTE CAMBIAR ESTAS LINEAS EN CUESTION DEL OBJETO QUE SE USE ======================================

                //Agrego el objeto al array ya instanciado y seteado con todos sus valores correspondientes
                array_push($_listaAuxiliarObjetos,$objetoAuxiliar);  
            }
            
            $i++;
        }

        //Finalmente, cierro el archivo para que se guarden los cambios y o evitar errores.
        fclose($archivo);

        if (count($_listaAuxiliarObjetos) > 0)
        {
            foreach($_listaAuxiliarObjetos as $producto)
            {
                $producto->save();
            }

            $lecturaSalioBien = true;
        }

        //Una vez terminada la carga de objetos al array auxiliar, lo retorno.
        return $_listaAuxiliarObjetos;
    }
}
?>