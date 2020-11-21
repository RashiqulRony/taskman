<?php

namespace Laravel\Spark\Configuration;

use FontLib\Table\Type\name;
use Laravel\Spark\Spark;
use Laravel\Cashier\Cashier;
use Braintree\ClientToken as BraintreeClientToken;

trait ProjectTemplateVariable
{
    /**
     * Get the default project template variable for software.
     *
     * @return array
     */
    public static function softwareTemplate ()
    {
        return [
            'nav_list' => [
                ['name' => 'Idea', 'list' => ['Software Ideas']],
                ['name' => 'Scope', 'list' => ['Bugs']],
            ],
            'nav_board' => [
                [
                    'name' => 'Boards',
                    'board' => [
                        [
                            'name' => 'Backlog',
                            'column' => [
                                ['name' => 'Backlog', 'progress' => 0],
                                ['name' => 'Need More Info', 'progress' => 0],
                                ['name' => 'In Review', 'progress' => 0],
                                ['name' => 'Ready For Devs', 'progress' => 0],
                            ]
                        ],
                        [
                            'name' => 'Development',
                            'column' => [
                                ['name' => 'ToDo', 'progress' => 0],
                                ['name' => 'Need More Info', 'progress' => 0],
                                ['name' => 'In Progress', 'progress' => 25],
                                ['name' => 'Bug', 'progress' => 125],
                                ['name' => 'PR Ready', 'progress' => 50],
                                ['name' => 'Ready For Testing', 'progress' => 50],
                                ['name' => 'Ready For Production', 'progress' => 75],
                                ['name' => 'Send To Production', 'progress' => 100]
                            ]
                        ],
                        [
                            'name' => 'Testing',
                            'column' => [
                                ['name' => 'Ready For Testing', 'progress' => 50],
                                ['name' => 'Testing', 'progress' => 625],
                                ['name' => 'Regress Bug', 'progress' => 125],
                                ['name' => 'Test Passed', 'progress' => 75]

                            ]
                        ],
                        [
                            'name' => 'On Production',
                            'column' => [
                                ['name' => 'Regress Bug', 'progress' => 125],
                                ['name' => 'On Production', 'progress' => 100]
                            ]
                        ],
                    ],
                    'rules' => [
                        [
                            'name' => 'Move Backlog >> Ready For Devs  To  Development >> ToDo',
                            'from_board' => 'Backlog',
                            'from_column' => 'Ready For Devs',
                            'to_board' => 'Development',
                            'to_column' => 'ToDo',
                        ],
                        [
                            'name' => 'Move Development >> Ready For Testing  :To: Testing >> Ready For Testing',
                            'from_board' => 'Development',
                            'from_column' => 'Ready For Testing',
                            'to_board' => 'Testing',
                            'to_column' => 'Ready For Testing',
                        ],
                        [
                            'name' => 'Move Development >> Send To Production :To: On Production >> On Production',
                            'from_board' => 'Development',
                            'from_column' => 'Send To Production',
                            'to_board' => 'On Production',
                            'to_column' => 'On Production',
                        ],
                        [
                            'name' => 'Move Testing >> Regress Bug :To: Development >> Bug',
                            'from_board' => 'Testing',
                            'from_column' => 'Regress Bug',
                            'to_board' => 'Development',
                            'to_column' => 'Bug',
                        ],
                        [
                            'name' => 'Move Testing >> Test Passed :To: Development >> Bug',
                            'from_board' => 'Testing',
                            'from_column' => 'Test Passed',
                            'to_board' => 'Development',
                            'to_column' => 'Ready For Production',
                        ],
                        [
                            'name' => 'Move On Production >> Regress Bug :To: Development >> Bug',
                            'from_board' => 'On Production',
                            'from_column' => 'Regress Bug',
                            'to_board' => 'Development',
                            'to_column' => 'Bug',
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * Get the default project template variable for Design.
     *
     * @return array
     */
    public static function DesignProjectTemplate ()
    {
        return [
            'nav_list' => [
                ['name' => 'Ideas', 'list' => ['Design Ideas']],
                ['name' => 'Scopes', 'list' => ['Logos', 'Site']],
            ],
            'nav_board' => [
                [
                    'name' => 'Boards',
                    'board' => [
                        [
                            'name' => 'Design Concepts',
                            'column' => [
                                ['name' => 'Ideas', 'progress' => 0],
                                ['name' => 'Need More Info', 'progress' => 0],
                                ['name' => 'In Review', 'progress' => 0],
                                ['name' => 'Ready For Design', 'progress' => 0],
                            ]
                        ],
                        [
                            'name' => 'Design',
                            'column' => [
                                ['name' => 'ToDo', 'progress' => 0],
                                ['name' => 'Need More Info', 'progress' => 0],
                                ['name' => 'Need Revisions', 'progress' => 125],
                                ['name' => 'In Progress', 'progress' => 25],
                                ['name' => 'Ready For Review', 'progress' => 50],
                                ['name' => 'Approved', 'progress' => 75],
                                ['name' => 'Complete', 'progress' => 100]
                            ]
                        ],
                        [
                            'name' => 'In Review',
                            'column' => [
                                ['name' => 'Waiting For Review', 'progress' => 50],
                                ['name' => 'In Review', 'progress' => 625],
                                ['name' => 'Needs Revisions', 'progress' => 125],
                                ['name' => 'Approved', 'progress' => 75]

                            ]
                        ],
                    ],
                    'rules' => [
                        [
                            'name' => 'Move Design Concepts >> Ready For Design  To  Design >> ToDo',
                            'from_board' => 'Design Concepts',
                            'from_column' => 'Ready For Design',
                            'to_board' => 'Design',
                            'to_column' => 'ToDo',
                        ],
                        [
                            'name' => 'Move Design >> Ready For Review  To  In Review >> Waiting For Review',
                            'from_board' => 'Design',
                            'from_column' => 'Ready For Review',
                            'to_board' => 'In Review',
                            'to_column' => 'Waiting For Review',
                        ],
                        [
                            'name' => 'Move In Review  >> Need Revisions  To  Design >> Needs Revisions',
                            'from_board' => 'In Review',
                            'from_column' => 'Needs Revisions',
                            'to_board' => 'Design',
                            'to_column' => 'Needs Revisions',
                        ],
                        [
                            'name' => 'Move In Review >> Approved  To  Design >> Approved',
                            'from_board' => 'In Review',
                            'from_column' => 'Approved',
                            'to_board' => 'Design',
                            'to_column' => 'Approved',
                        ],

                    ]
                ],
            ]
        ];
    }

    /**
     * Get the default project template variable for Design.
     *
     * @return array
     */
    public static function BasicProjectTemplate ()
    {
        return [
            'nav_list' => [
                ['name' => 'Ideas', 'list' => ['Company Ideas']],
                ['name' => 'Scopes', 'list' => ['ToDos']],
            ],
            'nav_board' => [
                [
                    'name' => 'Boards',
                    'board' => [
                        [
                            'name' => 'ToDos',
                            'column' => [
                                ['name' => 'ToDo', 'progress' => 0],
                                ['name' => 'Need More Info', 'progress' => 125],
                                ['name' => 'In Progress', 'progress' => 25],
                                ['name' => 'Ready For Review', 'progress' => 50],
                                ['name' => 'Complete', 'progress' => 100]
                            ]
                        ]
                    ],
                    'rules' => []
                ],
            ]
        ];
    }

    /**
     * Get the default project template variable for Design.
     *
     * @return array
     */
    public static function WritersProjectTemplate ()
    {
        return [
            'nav_list' => [
                ['name' => 'Ideas', 'list' => ['Content Ideas']],
                ['name' => 'Scopes', 'list' => ['My First Topic']],
            ],
            'nav_board' => [
                [
                    'name' => 'Boards',
                    'board' => [
                        [
                            'name' => 'Content Board',
                            'column' => [
                                ['name' => 'Topic Ideas', 'progress' => 0],
                                ['name' => 'In Planning', 'progress' => 125],
                                ['name' => 'Ready to Write', 'progress' => 25],
                                ['name' => 'In Writing', 'progress' => 50],
                                ['name' => 'Editing / Review', 'progress' => 75],
                                ['name' => 'Ready to Publish', 'progress' => 875],
                                ['name' => 'Published', 'progress' => 100]
                            ]
                        ]
                    ],
                    'rules' => []
                ],
            ]
        ];
    }

}
