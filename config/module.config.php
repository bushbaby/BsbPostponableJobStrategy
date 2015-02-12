<?php

return [
    'slm_queue' => array(
        'strategy_manager' => array(
            'factories' => array(
                'BsbPostponableJobStrategy\Strategy\PostponableJobStrategy'
                => 'BsbPostponableJobStrategy\Strategy\Factory\PostponableJobStrategyFactory',
            ),
        ),
    )
];
