<?php

namespace Terminal42\FineUploaderBundle\RequestHandler;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\DataContainer;
use Contao\System;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Terminal42\FineUploaderBundle\Widget\BackendWidget;

class BackendHandler
{
    use HandlerTrait;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var ContaoFrameworkInterface
     */
    private $framework;

    /**
     * BackendHandler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param ContaoFrameworkInterface $framework
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, ContaoFrameworkInterface $framework)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->framework       = $framework;
    }

    /**
     * Handle upload request
     *
     * @param Request       $request
     * @param DataContainer $dc
     *
     * @return JsonResponse
     *
     * @throw \RuntimeException
     */
    public function handleUploadRequest(Request $request, DataContainer $dc)
    {
        $this->validateRequest($request, ContaoCoreBundle::SCOPE_BACKEND);

        /** @var BackendWidget $widget */
        $widget = new $GLOBALS['BE_FFL']['fineUploader'](
            [
                'strTable' => $dc->table,
                'id'       => $dc->id,
                'name'     => $request->request->get('name'),
            ], $dc
        );

        return $this->getUploadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Handle reload widget request
     *
     * @param Request       $request
     * @param DataContainer $dc
     *
     * @return Response
     *
     * @throw \Exception
     * @throw \RuntimeException
     */
    public function handleReloadRequest(Request $request, DataContainer $dc)
    {
        $id    = $request->query->get('id');
        $field = $dc->field = $request->request->get('name');

        // Handle the keys in "edit multiple" mode
        if ($request->query->get('act') === 'editAll') {
            $id    = preg_replace('/.*_([0-9a-zA-Z]+)$/', '$1', $field);
            $field = preg_replace('/(.*)_[0-9a-zA-Z]+$/', '$1', $field);
        }

        $dca = $GLOBALS['TL_DCA'][$dc->table];

        // The field does not exist
        if (!isset($dca['fields'][$field])) {
            throw new \Exception(sprintf('Field "%s" does not exist in DCA "%s"', $field, $dc->table));
        }

        // Call the load_callback
        $this->triggerLoadCallback($dca, $dc, $field, $id);

        // Build the attributes based on the "eval" array
        $attributes = $dca['fields'][$field]['eval'];

        // Add some extra attributes required by the widget
        $attributes['id']           = $dc->field;
        $attributes['name']         = $dc->field;
        $attributes['value']        = $this->parseValue($this->framework, $request->request->get('value'));
        $attributes['strTable']     = $dc->table;
        $attributes['strField']     = $field;
        $attributes['activeRecord'] = $dc->activeRecord;

        /** @var BackendWidget $widget */
        $widget = new $GLOBALS['BE_FFL']['fineUploader']($attributes);

        return $this->getReloadResponse($this->eventDispatcher, $request, $widget);
    }

    /**
     * Trigger the load callback
     *
     * @param array         $dca
     * @param DataContainer $dc
     * @param string        $field
     * @param int           $id
     *
     * @throws \InvalidArgumentException
     */
    private function triggerLoadCallback(array $dca, DataContainer $dc, $field, $id)
    {
        $value = null;

        /** @var \Contao\Database $db */
        $db = $this->framework->createInstance('\Contao\Database');

        // Load the value
        if ($dca['config']['dataContainer'] === 'File') {
            $value = $GLOBALS['TL_CONFIG'][$field];
        } elseif ($id > 0 && $db->tableExists($dc->table)) {
            $row = $db->prepare(sprintf('SELECT * FROM %s WHERE id=?', $dc->table))->execute($id);

            // The record does not exist
            if ($row->numRows < 1) {
                throw new \InvalidArgumentException(
                    sprintf('A record with the ID "%s" does not exist in table "%s"', $id, $dc->table)
                );
            }

            $value            = $row->$field;
            $dc->activeRecord = $row;
        }

        // Trigger the callbacks
        if (is_array($dca['fields'][$field]['load_callback'])) {
            foreach ($dca['fields'][$field]['load_callback'] as $callback) {
                if (is_array($callback)) {
                    $value = System::importStatic($callback[0])->{$callback[1]}($value, $dc);
                } elseif (is_callable($callback)) {
                    $value = $callback($value, $dc);
                }
            }
        }
    }
}
