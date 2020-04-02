<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Class ContactController
 * @author CammoSarl
 */
class ContactController extends AppController
{
    public function overview()
    {
		#$this->request->allowMethod(['get']);
        if ($this->request->is('post')) {
        }
        $this->render();
    }
}
