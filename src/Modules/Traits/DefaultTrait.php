<?php

namespace Modules\Traits;

use Modules\Module;

/**
 * Trait dedicato alla gestione delle operazioni di visualizzazione per i template di modifica e aggiunta righe.
 *
 * @since 2.5
 */
trait DefaultTrait
{
    public function registerVisit()
    {
        $user = $this->auth->getUser();
        // Rimozione record precedenti sulla visita della pagina
        $this->database->delete('zz_semaphores', [
            'id_utente' => $user['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);

        // Creazione nuova visita
        $this->database->insert('zz_semaphores', [
            'id_utente' => $user['id'],
            'posizione' => $args['module_id'].', '.$args['record_id'],
        ]);
    }

    public function getOperations(Module $module, ?int $id_record)
    {
        // Elenco delle operazioni
        $operations = $this->database->fetchArray('SELECT `zz_operations`.*,
            `zz_users`.`username`,
            DATE(`zz_users`.`created_at`) as date
        FROM `zz_operations`
            JOIN `zz_users` ON `zz_operations`.`id_utente` = `zz_users`.`id`
            WHERE id_module = '.prepare($module->id).' AND id_record = '.prepare($id_record).'
        ORDER BY `created_at` ASC LIMIT 200');

        foreach ($operations as $key => $operation) {
            $description = $operation['op'];
            $icon = 'pencil-square-o';
            $color = null;
            $tags = null;

            switch ($operation['op']) {
                case 'add':
                    $description = tr('Creazione');
                    $icon = 'plus';
                    $color = 'success';
                    break;

                case 'update':
                    $description = tr('Modifica');
                    $icon = 'pencil';
                    $color = 'info';
                    break;

                case 'delete':
                    $description = tr('Eliminazione');
                    $icon = 'times';
                    $color = 'danger';
                    break;

                default:
                    $tags = ' class="timeline-inverted"';
                    break;
            }

            $operation['tags'] = $tags;
            $operation['color'] = $color;
            $operation['icon'] = $icon;
            $operation['description'] = $description;

            $operations[$key] = $operation;
        }

        return collect($operations);
    }
}
