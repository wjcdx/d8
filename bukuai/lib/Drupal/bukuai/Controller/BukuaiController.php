<?php

/**
 * @file
 * Contains \Drupal\bukuai\Controller\BukuaiController.
 */

namespace Drupal\bukuai\Controller;

use Drupal\bukuai\BukuaiManager;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for bukuai routes.
 */
class BukuaiController implements ContainerInjectionInterface {

  /**
   * Bukuai manager service.
   *
   * @var \Drupal\bukuai\BukuaiManager
   */
  protected $bukuaiManager;

  /**
   * Constructs a BukuaiController object.
   *
   * @param 
   *  
   */
	public function __construct(BukuaiManager $bkMgr) {
		$this->bukuaiManager = $bkMgr;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('bukuai.manager')
    );
  }

  /**
   * Returns bukuai page for a given bukuai.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   The bukuai to render the page for.
   *
   * @return array
   *   A render array.
   */
	public function splitPage($category, $no) {

		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/drag&drop.js' , array('weight' => 1));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/CombineEffect.js' , array('weight' => 2));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Combiner.js' , array('weight' => 3));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Displayer.js' , array('weight' => 4));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Character.js' , array('weight' => 5));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Partial.js' , array('weight' => 6));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/SplitEffect.js' , array('weight' => 7));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Splitter.js' , array('weight' => 8));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/UiElement.js' , array('weight' => 9));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/PartialManager.js' , array('weight' => 10));
		drupal_add_js(drupal_get_path('module', 'bukuai') . '/js/Canvas.js' , array('weight' => 11));

		$parts = array();
		$next = url('split/id/0');
		$count = $this->bukuaiManager->tree_count();

		if ($count > 0) {

			$field = 'no';
			$conds = array();

			if ($category == 'no') {
				$field = 'no';
			} else {
				$field = 'pid';
			}

			if ($no == 0) {
				$field = 'pid';
				$no = rand(1, $count);
			}

			$conds[$field] = $no;
			$entries = $this->bukuaiManager->tree_load($conds);
			$tree = reset($entries);

			if ($tree) {
				$id = $tree->pid;
				$no = $tree->no;
				$tree_str = $tree->tree;

				$next = url('split/id/' . $this->bukuaiManager->next_id($id, $count));

				$tree_ary = $this->bukuaiManager->fmtstr_array($tree_str);
				$this->bukuaiManager->flat_tree('1', $no, $tree_ary, $parts);
			}
		}

		$page = array(
			'#theme' => 'bukuai_split_combine',
			'#parts' => $parts,
			'#next' => $next,
		);
		return $page;
	}
}
