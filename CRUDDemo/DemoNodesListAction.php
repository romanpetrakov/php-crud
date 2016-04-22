<?php

namespace CRUDDemo;

use OLOG\BT;
use OLOG\CRUD\CRUDTable;
use OLOG\CRUD\CRUDFormRow;
use OLOG\CRUD\CRUDTableColumn;
use OLOG\CRUD\CRUDTableWidgetDelete;
use OLOG\CRUD\CRUDTableWidgetText;
use OLOG\CRUD\CRUDTableWidgetTextWithLink;
use OLOG\CRUD\CRUDFormWidgetInput;

class DemoNodesListAction
{
    static public function getUrl()
    {
        return '/nodes';
    }

    public function action()
    {
        \OLOG\Exits::exit403If(!Auth::currentUserHasAnyOfPermissions([1]));

        $html = '';

        $html .= CRUDTable::html(
            DemoNode::class,
            \OLOG\CRUD\CRUDForm::html(
                new DemoNode(),
                [
                    new CRUDFormRow(
                        'Title',
                        new CRUDFormWidgetInput('title')
                    )
                ]
            ),
            [
                new CRUDTableColumn(
                    'Edit',
                    new CRUDTableWidgetText('{this->title}')
                ),
                new CRUDTableColumn(
                    'Edit',
                    new CRUDTableWidgetTextWithLink(
                        '{' . DemoNode::class . '.{this->id}->title}',
                        DemoNodeEditAction::getUrl('{this->id}')
                    )
                ),
                new CRUDTableColumn(
                    'Delete',
                    new CRUDTableWidgetDelete()
                ),
            ],
            [],
            'title'
        );

        DemoLayoutTemplate::render($html, 'Nodes', self::getBreadcrumbsArr());
    }

    static public function getBreadcrumbsArr()
    {
        return array_merge(DemoMainPageAction::breadcrumbsArr(), [BT::a(self::getUrl(), 'Nodes')]);
    }
}