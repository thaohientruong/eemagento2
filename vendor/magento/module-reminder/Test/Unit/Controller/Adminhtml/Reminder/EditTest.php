<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reminder\Test\Unit\Controller\Adminhtml\Reminder;

class EditTest extends AbstractReminderControllerTest
{
    public function testEditActionWithModelException()
    {
        $this->initRuleWithException();

        $this->messageManager->expects($this->once())->method('addError');
        $this->redirect('adminhtml/*/', []);

        $model = new \Magento\Reminder\Controller\Adminhtml\Reminder\Edit(
            $this->context,
            $this->coreRegistry,
            $this->ruleFactory,
            $this->conditionFactory,
            $this->dataFilter
        );
        $model->execute();
    }

    /**
     * Run test execute method
     *
     * @param int|bool $dataFlag
     *
     * @dataProvider dataProviderExecute
     */
    public function testExecute($dataFlag)
    {
        $this->initRule();

        $this->condition->expects($this->once())
            ->method('setJsFormObject')->with('rule_conditions_fieldset');

        $this->rule->expects($this->once())->method('getConditions')->willReturn($this->condition);
        $this->rule->expects($this->any())->method('getName')->willReturn($this->condition);
        $this->session->expects($this->any())->method('getPageData')->willReturn($dataFlag);
        $this->rule->expects($this->any())->method('addData');

        $this->view->expects($this->any())->method('getLayout')->willReturn($this->layout);
        $this->layout->expects($this->any())->method('getBlock')->willReturn($this->block);
        $this->block->expects($this->once())
            ->method('setActive')->with('Magento_Reminder::promo_reminder')->willReturn($this->block);
        $this->block->expects($this->once())
            ->method('getMenuModel')->with()->willReturn($this->menuModel);
        $this->block->expects($this->any())->method('addLink')->willReturn($this->block);
        $this->block->expects($this->once())->method('setData')->willReturn($this->block);
        $this->view->expects($this->any())->method('getPage')->willReturn($this->page);
        $this->page->expects($this->any())->method('getConfig')->willReturn($this->config);
        $this->config->expects($this->any())->method('getTitle')->willReturn($this->titleMock);
        $this->titleMock->expects($this->any())->method('prepend')->willReturn('title');
        $this->view->expects($this->once())->method('renderLayout');
        $this->item->expects($this->any())->method('getTitle')->willReturn('title');
        $this->menuModel->expects($this->once())
            ->method('getParentItems')->with('Magento_Reminder::promo_reminder')->willReturn([$this->item]);

        $model = new \Magento\Reminder\Controller\Adminhtml\Reminder\Edit(
            $this->context,
            $this->coreRegistry,
            $this->ruleFactory,
            $this->conditionFactory,
            $this->dataFilter
        );
        $model->execute();
    }

    public function dataProviderExecute()
    {
        return [
            [
                'dataFlag' => null,
            ],
            [
                'dataFlag' => 1,
            ]
        ];
    }
}
