<?php

namespace Drupal\vbo_populate_event_group_type\Plugin\Action;

use Drupal\node\Entity\Node;
use Drupal\views_bulk_operations\Action\ViewsBulkOperationsActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Content moderation publish node.
 *
 * @Action(
 *   id = "vbo_populate_event_group_type",
 *   label = @Translation("Populate Event_group_type"),
 *   type = "node",
 *   confirm = TRUE
 * )
 */

class PopulateEventGroupType extends ViewsBulkOperationsActionBase {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function execute(ContentEntityInterface $entity = NULL) {

    try {
      $database = \Drupal::database();    

      $temp1 = $entity->get("nid")->getString();  
      
      $query = $database->select('group__field_group_type', 'gfgt'); 
      $query->addField('gfgt', 'field_group_type_target_id', 'grouptype'); 
      $query->join('group_content_field_data', 'gcfd', 'gcfd.gid = gfgt.entity_id');      
      $result = $query 
        ->condition('gcfd.entity_id', $temp1, '=')
        ->execute();         
      
      foreach ($result as $record) {        
        $entity->set("field_event_group_type", $record->grouptype);
        $entity->save();
      }   

    } catch (Exception $e) {
      \Drupal::logger('PopulateEventGroupType')->notice('error '.$e);
     }

    return $this->t('All worked');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    if ($object->getEntityType() === 'node') {
      $access = $object->access('update', $account, TRUE)
        ->andIf($object->status->access('edit', $account, TRUE));
      return $return_as_object ? $access : $access->isAllowed();
    }

    // Other entity types may have different
    // access methods and properties.
    return TRUE;
  }
}