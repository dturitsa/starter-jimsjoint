<?php

/**
 * Order handler
 *
 * Implement the different order handling usecases.
 *
 * controllers/welcome.php
 *
 * ------------------------------------------------------------------------
 */
class Order extends Application
{

    public function __construct()
    {
        parent::__construct();
    }

    // start a new order
    public function neworder()
    {
        $order_num = $this->orders->highest() + 1;

        $neworder = $this->orders->create();

        $neworder->num = $order_num;

        $neworder->date = date();

        $neworder->status = 'a';

        $neworder->total = 0;

        $this->orders->add($neworder);

        redirect('/order/display_menu/' . $order_num);
    }

    // add to an order
    public function display_menu($order_num = null)
    {

        if ($order_num == null) {
            redirect('/order/neworder');
        }

        $this->data['pagebody'] = 'show_menu';

        $this->data['order_num'] = $order_num;

        $this->data['title'] = "Order # "
        . ' (' . number_format($this->orders->total($order_num), 2) . ')';

        // Make the columns
        $this->data['meals']  = $this->make_column('m');
        $this->data['drinks'] = $this->make_column('d');
        $this->data['sweets'] = $this->make_column('s');

        // Bit of a hokey patch here, to work around the problem of the template
        // parser no longer allowing access to a parent variable inside a
        // child loop - used for the columns in the menu display.
        // this feature, formerly in CI2.2, was removed in CI3 because
        // it presented a security vulnerability.
        //
        // This means that we cannot reference order_num inside of any of the
        // variable pair loops in our view, but must instead make sure
        // that any such substitutions we wish make are injected into the
        // variable parameters
        // Merge this fix into your origin/master for the lab!
        $this->hokeyfix($this->data['meals'], $order_num);
        $this->hokeyfix($this->data['drinks'], $order_num);
        $this->hokeyfix($this->data['sweets'], $order_num);
        // end of hokey patch

        $this->render();
    }

    // inject order # into nested variable pair parameters
    public function hokeyfix($varpair, $order)
    {
        foreach ($varpair as &$record) {
            $record->order_num = $order;
        }

    }

    // make a menu ordering column
    public function make_column($category)
    {
        return $this->menu->some('category', $category);
    }

    // add an item to an order
    public function add($order_num, $item)
    {
        $this->orders->add_item($order_num, $item);
        redirect('/order/display_menu/' . $order_num);
    }

    // checkout
    public function checkout($order_num)
    {
        $this->data['title']     = 'Checking Out';
        $this->data['pagebody']  = 'show_order';
        $this->data['order_num'] = $order_num;
        $this->data['items'] = $this->orders->details($order_num);

        $this->render();
    }

    // proceed with checkout
    public function commit($order_num)
    {
        //FIXME
        redirect('/');
    }

    // cancel the order
    public function cancel($order_num)
    {
        //FIXME
        redirect('/');
    }

}
