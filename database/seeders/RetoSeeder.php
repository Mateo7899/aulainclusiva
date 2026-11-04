<?php

namespace Database\Seeders;

use App\Models\Reto;
use Illuminate\Database\Seeder;

class RetoSeeder extends Seeder
{
    public function run(): void
    {
        $retos = [
            [
                'titulo' => 'Instrucciones en pasos',
                'microleccion' => 'Una práctica de gestión documental tiene muchas tareas seguidas: clasificar, ordenar, foliar, describir. Si se pide todo de una vez, abruma. Divídela en pasos cortos y numerados, y déjalos visibles para que cada estudiante sepa qué hace ahora y qué sigue.',
                'tipo_actividad' => 'ordenar',
                'actividad' => [
                    'instruccion' => 'Ordena los pasos que sigues para guiar una práctica de organización de un expediente.',
                    'pasos' => [
                        'Define la práctica: organizar un expediente a partir de documentos sueltos',
                        'Divídela en pasos cortos: clasificar, ordenar, foliar y describir',
                        'Escribe los pasos numerados en el tablero o en la guía',
                        'Léelos en voz alta con el grupo antes de empezar',
                        'Revisa con el grupo cada paso a medida que lo completan',
                    ],
                    'explicacion' => 'Primero defines la práctica, luego la divides, la haces visible, la lees en conjunto y la acompañas.',
                ],
            ],
            [
                'titulo' => 'Apoyo visual',
                'microleccion' => 'Ver y escuchar a la vez ayuda a entender y recordar. En temas con varias etapas, como el ciclo vital del documento, un esquema o una línea de tiempo vale más que una explicación solo hablada.',
                'tipo_actividad' => 'escenario',
                'actividad' => [
                    'escena' => '🗄️ 📁 📄 🗃️',
                    'situacion' => 'Vas a explicar el ciclo vital del documento: archivo de gestión, archivo central y archivo histórico. Hasta ahora lo has explicado solo hablando y varios estudiantes confunden las etapas. ¿Qué haces?',
                    'opciones' => [
                        ['texto' => 'Hablar más despacio y repetir la misma explicación', 'correcta' => false, 'feedback' => 'Ayuda un poco, pero sigue dependiendo solo de lo que escuchan.'],
                        ['texto' => 'Dibujar una línea de tiempo con las tres etapas y un ejemplo de documento en cada una, mientras explicas', 'correcta' => true, 'feedback' => 'Ver las etapas en orden mientras las escuchan ayuda a distinguirlas y recordarlas.'],
                        ['texto' => 'Entregar el texto completo de la norma para que lo lean por su cuenta', 'correcta' => false, 'feedback' => 'Un texto normativo largo, sin apoyo, puede aumentar la dificultad.'],
                    ],
                ],
            ],
            [
                'titulo' => 'Tiempo flexible',
                'microleccion' => 'Actividades como clasificar series documentales o elaborar un inventario requieren atención al detalle. Dar tiempo extra permite demostrar lo aprendido sin presión. Si lo anuncias desde el inicio y es para todos, nadie se siente señalado.',
                'tipo_actividad' => 'mito',
                'actividad' => [
                    'instruccion' => '¿Mito o realidad? Clasifica cada afirmación.',
                    'categorias' => ['Mito', 'Realidad'],
                    'tarjetas' => [
                        ['texto' => 'Dar tiempo extra en una práctica de clasificación documental baja el nivel de exigencia.', 'categoria' => 0, 'explicacion' => 'La práctica es igual de exigente; solo cambia el tiempo disponible para hacerla.'],
                        ['texto' => 'Avisar desde el inicio que quien lo necesite tendrá tiempo adicional evita que alguien se sienta señalado.', 'categoria' => 1, 'explicacion' => 'Cuando la opción es para todos, nadie queda expuesto.'],
                        ['texto' => 'Si se da más tiempo, hay que simplificar el ejercicio.', 'categoria' => 0, 'explicacion' => 'El ejercicio puede mantener su dificultad; lo que se ajusta es el tiempo.'],
                        ['texto' => 'El tiempo flexible solo sirve en los exámenes; en prácticas como el inventario documental no aplica.', 'categoria' => 0, 'explicacion' => 'También ayuda en prácticas, lecturas de normas y trabajos en clase.'],
                    ],
                ],
            ],
            [
                'titulo' => 'Instrucción doble',
                'microleccion' => 'Si una consigna solo se dice en voz alta, quien se distrajo un momento la pierde. Dila y además déjala escrita, por ejemplo cómo diligenciar el formato de inventario documental.',
                'tipo_actividad' => 'completar',
                'actividad' => [
                    'frase' => 'Cuando des una consigna, como cuáles columnas llenar en el formato de inventario documental, dila en voz {0} y escríbela también en el {1}, para que cada estudiante pueda {2} cuando la necesite.',
                    'huecos' => [
                        ['opciones' => ['alta', 'baja', 'rápida'], 'correcta' => 'alta'],
                        ['opciones' => ['tablero', 'archivador', 'pasillo'], 'correcta' => 'tablero'],
                        ['opciones' => ['volver a consultarla', 'olvidarla', 'adivinarla'], 'correcta' => 'volver a consultarla'],
                    ],
                    'explicacion' => 'Oral más escrita: cada estudiante elige cómo consultar la consigna.',
                ],
            ],
            [
                'titulo' => 'Lectura accesible',
                'microleccion' => 'Los materiales de gestión documental suelen ser densos: normas, acuerdos, tablas de retención. Un texto ordenado reduce el esfuerzo de lectura. Usa letra grande, párrafos cortos, títulos claros y resalta las ideas clave.',
                'tipo_actividad' => 'clasificar',
                'actividad' => [
                    'instruccion' => 'Clasifica cada característica de un material de lectura, como una guía sobre tablas de retención documental.',
                    'categorias' => ['Facilita la lectura', 'La dificulta'],
                    'tarjetas' => [
                        ['texto' => 'Letra de tamaño grande', 'categoria' => 0, 'explicacion' => 'Una letra más grande hace el texto más cómodo de leer.'],
                        ['texto' => 'Párrafos cortos con espacio entre ellos', 'categoria' => 0, 'explicacion' => 'Los espacios dan pausas y orientan al lector.'],
                        ['texto' => 'Un solo bloque largo con la norma copiada, sin resumir ni resaltar nada', 'categoria' => 1, 'explicacion' => 'Cuesta saber dónde está uno y cuáles son las ideas clave.'],
                        ['texto' => 'Una tabla de retención con celdas diminutas y texto apretado', 'categoria' => 1, 'explicacion' => 'El tamaño reducido y la falta de espacio dificultan seguir cada fila.'],
                        ['texto' => 'Títulos, subtítulos y conceptos clave resaltados', 'categoria' => 0, 'explicacion' => 'Ayudan a ubicarse y a distinguir lo importante.'],
                        ['texto' => 'Letra muy decorada o cursiva en todo el texto', 'categoria' => 1, 'explicacion' => 'Las letras muy decoradas se confunden con facilidad.'],
                    ],
                ],
            ],
            [
                'titulo' => 'Opciones de respuesta',
                'microleccion' => 'No todos demuestran lo que aprendieron de la misma forma. Ofrece varias maneras de responder: oral, escrita, con un esquema, con un caso práctico o armando un expediente.',
                'tipo_actividad' => 'emparejar',
                'actividad' => [
                    'instruccion' => 'Une cada objetivo de aprendizaje con una forma alternativa de demostrarlo.',
                    'parejas' => [
                        ['izq' => 'Explicar el ciclo vital del documento', 'der' => 'Hacer un esquema o una línea de tiempo'],
                        ['izq' => 'Describir cómo se organiza un expediente', 'der' => 'Armar un expediente real y explicarlo en voz alta'],
                        ['izq' => 'Diferenciar serie y subserie documental', 'der' => 'Construir un mapa conceptual'],
                        ['izq' => 'Reconocer la disposición final en una tabla de retención', 'der' => 'Resolver un caso práctico con tarjetas'],
                    ],
                ],
            ],
            [
                'titulo' => 'Refuerzo positivo',
                'microleccion' => 'Un reconocimiento funciona mejor cuando dice exactamente qué hizo bien el estudiante. Lo específico motiva más que lo general, y le indica qué repetir.',
                'tipo_actividad' => 'escenario',
                'actividad' => [
                    'escena' => '📁 ✅ 🌟',
                    'situacion' => 'Un estudiante que casi nunca participa hoy organizó un expediente completo: documentos en orden, foliados y con su hoja de control. Quieres reconocerlo. ¿Qué le dices?',
                    'opciones' => [
                        ['texto' => '«¡Muy bien!»', 'correcta' => false, 'feedback' => 'Es amable, pero no le dice qué hizo bien.'],
                        ['texto' => '«Ordenaste los documentos, los foliaste y completaste la hoja de control. Se nota el cuidado.»', 'correcta' => true, 'feedback' => 'Es específico: el estudiante sabe qué repetir la próxima vez.'],
                        ['texto' => '«Por fin lo lograste.»', 'correcta' => false, 'feedback' => 'Puede sonar a reproche en vez de reconocimiento.'],
                    ],
                ],
            ],
            [
                'titulo' => 'Entorno ordenado',
                'microleccion' => 'Un espacio con menos estímulos ayuda a mantener la atención. En clases de gestión documental hay muchos elementos a la vista: cajas, carpetas, proyector. Revisa el ruido, el material sobre la mesa y lo que compite por la atención.',
                'tipo_actividad' => 'emparejar',
                'actividad' => [
                    'instruccion' => 'Une cada fuente de distracción con un ajuste que la reduce.',
                    'parejas' => [
                        ['izq' => 'Ruido que entra por el pasillo', 'der' => 'Ubicarlo lejos de la puerta'],
                        ['izq' => 'Muchas cajas y carpetas sobre la mesa', 'der' => 'Dejar solo los documentos de la práctica del día'],
                        ['izq' => 'Diapositivas sobrecargadas de texto', 'der' => 'Usar diapositivas con una sola idea cada una'],
                        ['izq' => 'Compañeros conversando cerca durante la práctica', 'der' => 'Sentarlo cerca del docente y del tablero'],
                    ],
                ],
            ],
            [
                'titulo' => 'Pausas activas',
                'microleccion' => 'Las jornadas de práctica con papel y pantalla, como foliar, digitalizar o describir documentos, cansan la vista y las manos. Una pausa corta de movimiento a mitad de la clase ayuda a recuperar la atención de todo el grupo.',
                'tipo_actividad' => 'ordenar',
                'actividad' => [
                    'instruccion' => 'Ordena los pasos de una pausa activa en una práctica larga de gestión documental.',
                    'pasos' => [
                        'Avisa que habrá una pausa de 2 minutos',
                        'Explica el movimiento, por ejemplo estirar muñecas, cuello y hombros',
                        'Haz la pausa junto con el grupo',
                        'Termina con tres respiraciones profundas',
                        'Retoma la práctica con una consigna clara',
                    ],
                    'explicacion' => 'Se avisa, se explica, se hace, se calma el grupo y se retoma con claridad.',
                ],
            ],
            [
                'titulo' => 'Revisión de comprensión',
                'microleccion' => 'Preguntar «¿entendieron?» casi siempre recibe un «sí». Pide mejor que expliquen con sus propias palabras un concepto, como la diferencia entre serie y subserie documental.',
                'tipo_actividad' => 'completar',
                'actividad' => [
                    'frase' => 'En vez de preguntar «¿entendieron?», pide al estudiante que {0} con sus propias {1} qué es una serie documental. Así compruebas la {2} real y no solo un «sí» por compromiso.',
                    'huecos' => [
                        ['opciones' => ['explique', 'copie', 'memorice'], 'correcta' => 'explique'],
                        ['opciones' => ['palabras', 'notas', 'carpetas'], 'correcta' => 'palabras'],
                        ['opciones' => ['comprensión', 'velocidad', 'asistencia'], 'correcta' => 'comprensión'],
                    ],
                    'explicacion' => 'Explicarlo con sus palabras muestra si realmente comprendió.',
                ],
            ],
        ];

        foreach ($retos as $reto) {
            Reto::updateOrCreate(
                ['titulo' => $reto['titulo']],
                [
                    'microleccion' => $reto['microleccion'],
                    'tipo_actividad' => $reto['tipo_actividad'],
                    'actividad' => $reto['actividad'],
                    'activo' => true,
                ]
            );
        }
    }
}