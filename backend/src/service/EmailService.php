<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Usuario;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private string $mailFrom
    ) {}

    public function getMailFrom(): string
    {
        return $this->mailFrom;
    }

    /**
     * Envía el email de verificación de cuenta
     */
    public function sendVerificationEmail(Usuario $user): void
    {
        try {
            $htmlContent = "
                <html>
                <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                        <h1>¡Hola {$user->getNombre()} {$user->getApellido()}!</h1>
                        
                        <p>Gracias por registrarte en GemAcademy. Para completar tu registro, 
                        usa el siguiente código de verificación:</p>
                        
                        <div style='background-color: #f5f5f5; padding: 15px; margin: 20px 0; 
                                  text-align: center; font-size: 24px; letter-spacing: 5px;'>
                            <strong>{$user->getTokenVerificacion()}</strong>
                        </div>

                        <p>Este código expirará en 24 horas.</p>
                        
                        <p>Si no has creado una cuenta en GemAcademy, puedes ignorar este mensaje.</p>
                        
                        <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                        
                        <p style='font-size: 12px; color: #666;'>
                            Este es un mensaje automático, por favor no respondas a este correo.
                        </p>
                    </div>
                </body>
                </html>
            ";

            $email = (new Email())
                ->from($this->mailFrom)
                ->to($user->getEmail())
                ->subject('Verifica tu cuenta en GemAcademy')
                ->html($htmlContent);

            $this->mailer->send($email);
        } catch (\Exception $e) {
            // Log del error completo
            error_log("Error al enviar email de verificación: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            // Lanzar una excepción más específica
            throw new \RuntimeException(
                "Error al enviar el email de verificación. Por favor, verifica la configuración del servidor de correo. " .
                "Detalles: " . $e->getMessage()
            );
        }
    }

    /**
     * Envía el email de restablecimiento de contraseña
     */
    public function sendPasswordResetEmail(Usuario $user): void
    {
        $htmlContent = "
            <html>
            <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
                    <h1>¡Hola {$user->getNombre()} {$user->getApellido()}!</h1>
                    
                    <p>Has solicitado restablecer tu contraseña. Usa el siguiente código:</p>
                    
                    <div style='background-color: #f5f5f5; padding: 15px; margin: 20px 0; 
                              text-align: center; font-size: 24px; letter-spacing: 5px;'>
                        <strong>{$user->getTokenVerificacion()}</strong>
                    </div>
                    
                    <p>Si no has solicitado cambiar tu contraseña, puedes ignorar este mensaje.</p>
                    
                    <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                    
                    <p style='font-size: 12px; color: #666;'>
                        Este es un mensaje automático, por favor no respondas a este correo.
                    </p>
                </div>
            </body>
            </html>
        ";

        $email = (new Email())
            ->from($this->mailFrom)
            ->to($user->getEmail())
            ->subject('Restablece tu contraseña - GemAcademy')
            ->html($htmlContent);

        try {
            $this->mailer->send($email);
        } catch (\Exception $e) {
            throw new \RuntimeException('No se pudo enviar el email de restablecimiento');
        }
    }
}