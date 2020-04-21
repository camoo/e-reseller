<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Class BasketController
 * @author CamooSarl
 */
final class BasketController extends AppController
{
	public function overview()
	{
		$this->set('page_title', 'Votre Panier');
		$this->set('basket', $this->getBasketRepository());
		$this->render();
	}
}
