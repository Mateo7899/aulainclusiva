<?php

namespace Database\Seeders;

use App\Models\Reto;
use Illuminate\Database\Seeder;

class ConsejoSeeder extends Seeder
{
    public function run(): void
    {
        $consejos = [
            'Instrucciones en pasos' => 'En tu próxima práctica, por ejemplo organizar un expediente, escribe los pasos numerados en el tablero y léelos con el grupo antes de empezar. Ve marcando cada paso a medida que se completa.',
            'Apoyo visual' => 'Cuando expliques un tema con etapas, como el ciclo vital del documento, dibuja una línea de tiempo o un esquema en el tablero mientras hablas, con un ejemplo de documento en cada etapa.',
            'Tiempo flexible' => 'Al iniciar una práctica, anuncia que habrá tiempo adicional para quien lo necesite, sin pedir justificación y sin cambiar la dificultad del ejercicio.',
            'Instrucción doble' => 'Al dar una consigna, dila en voz alta y déjala escrita en el tablero o en la guía, por ejemplo las columnas que deben llenar en el formato de inventario documental.',
            'Lectura accesible' => 'Revisa tus guías: letra grande, párrafos cortos, títulos claros, ideas clave resaltadas y tablas con espacio suficiente en cada celda.',
            'Opciones de respuesta' => 'Ofrece al menos dos formas de demostrar un mismo aprendizaje, por ejemplo hacer un esquema o armar un expediente y explicarlo en voz alta.',
            'Refuerzo positivo' => 'Reconoce un logro concreto de un estudiante diciendo exactamente qué hizo bien, por ejemplo: «ordenaste y foliaste el expediente completo».',
            'Entorno ordenado' => 'Antes de la práctica, deja sobre cada mesa solo los documentos y materiales del día, y ubica cerca del tablero a quien se distrae con facilidad.',
            'Pausas activas' => 'A mitad de una práctica larga, avisa una pausa de 2 minutos, haz estiramientos de muñecas, cuello y hombros con el grupo y retoma con una consigna clara.',
            'Revisión de comprensión' => 'En lugar de preguntar «¿entendieron?», pide a un estudiante que explique con sus palabras un concepto, por ejemplo serie y subserie, y comprueba si coincide con lo que enseñaste.',
        ];

        foreach ($consejos as $titulo => $consejo) {
            Reto::where('titulo', $titulo)->update(['consejo' => $consejo]);
        }
    }
}
