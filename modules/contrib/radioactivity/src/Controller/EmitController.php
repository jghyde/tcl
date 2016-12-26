<?php

namespace Drupal\radioactivity\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Component\Serialization\Json;
use Drupal\radioactivity\Incident;
use Drupal\radioactivity\DefaultIncidentStorage;

/**
 * Controller routines for radioactivity emit routes.
 */
class EmitController extends ControllerBase {

  /**
   * Callback for /radioactivity/emit.
   *
   * @param Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   Response object.
   */
  public function emit(Request $request) {

    $storage = new DefaultIncidentStorage();

    $post_data = $request->getContent();

    if ($post_data) {

      $count = 0;
      $incidents = Json::decode($post_data);

      foreach ($incidents as $data) {

        $incident = Incident::createFromPostData($data);

        if ($incident->isValid()) {
          $storage->addIncident($incident);
          $count++;
        }
        else {
          return new JsonResponse(array('status' => 'error', 'message' => 'invalid incident (' . $count . ').'));
        }
      }

      return new JsonResponse(array('status' => 'ok', 'message' => $count . ' incidents added.'));
    }

    return new JsonResponse(array('status' => 'error', 'message' => 'Empty request.'));
  }

}
