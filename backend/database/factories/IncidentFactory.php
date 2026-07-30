<?php

namespace Database\Factories;

use App\Models\Incident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class IncidentFactory extends Factory
{
    protected $model = Incident::class;

    private static array $titles = [
        'Servidor caído en producción',
        'Error de conexión a base de datos',
        'Fallo en el servicio de autenticación',
        'Problema de rendimiento en API',
        'Error 500 en módulo de reportes',
        'Caída del servicio de correo',
        'Vulnerabilidad de seguridad detectada',
        'Disco lleno en servidor principal',
        'Timeout en consultas SQL',
        'Error en el despliegue automático',
        'Fallo en el balanceador de carga',
        'Certificado SSL vencido',
        'Problema con el backup nocturno',
        'Lentitud en el panel administrativo',
        'Error en la integración con pasarela de pago',
        'Fallo en el servicio de notificaciones',
        'Memoria insuficiente en contenedor Docker',
        'Error en la sincronización de datos',
        'Problema con permisos de usuario',
        'Caída del servicio de caché Redis',
        'Error en migración de base de datos',
        'Fallo en el sistema de logs',
        'Problema de conectividad en VPN',
        'Error en el procesamiento de archivos',
        'Incidencia con el CDN',
    ];

    public function definition(): array
    {
        $createdAt = $this->faker->dateTimeBetween('-6 months', 'now');
        $dueDate = $this->faker->dateTimeBetween($createdAt, '+2 months');
        $status = $this->faker->randomElement(['abierto', 'en_progreso', 'cerrado', 'vencido']);

        return [
            'title' => $this->faker->randomElement(self::$titles) . ' #' . $this->faker->unique()->numberBetween(1000, 9999),
            'description' => $this->faker->paragraphs(rand(2, 4), true),
            'priority' => $this->faker->randomElement(['baja', 'media', 'alta', 'critica']),
            'status' => $status,
            'user_id' => User::factory(),
            'assigned_id' => $this->faker->boolean(80) ? User::factory() : null,
            'due_date' => $dueDate->format('Y-m-d'),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
