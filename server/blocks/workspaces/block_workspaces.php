<?php

use block_totara_recently_viewed\card;
use block_totara_recently_viewed\card_resolver;
use block_totara_recently_viewed\settings_helper as settings;
use block_totara_recommendations\block_mode_factory;
use container_workspace\loader\workspace\loader;
use container_workspace\query\workspace\query;
use container_workspace\workspace;

defined('MOODLE_INTERNAL') || die();

class block_workspaces extends block_base
{
    function init()
    {
        $this->title = get_string('pluginname', 'block_workspaces');

        if (empty($this->config)) {
            $this->config = new stdClass();
        }

        $this->config->noi = 3;
    }

    function instance_allow_multiple(): bool
    {
        return false;
    }

    function has_config(): bool
    {
        return false;
    }

    function applicable_formats(): array
    {
        return ['all' => true, 'my' => true, 'tag' => false];
    }

    public function get_aria_role(): string
    {
        return 'navigation';
    }

    function get_required_javascript()
    {
        parent::get_required_javascript();
        $this->page->requires->js_call_amd('block_totara_recently_viewed/resize_blocks', 'init', [
            'blockid' => $this->instance->id,
        ]);
    }

    /**
     * @return object
     * @throws coding_exception
     */
    public function get_content(): object
    {
        if (!isloggedin() || isguestuser()) {
            return new stdClass();
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content       = new stdClass;
        $this->content->text = $this->render_cards($this->instance->id);

        return $this->content;
    }

    /**
     * @param  int  $instanceId
     *
     * @return string
     * @throws coding_exception
     */
    protected function render_cards(int $instanceId): string
    {
        global $OUTPUT;

        $context = $this->get_recommendations($instanceId);

        if (sizeof($context['cards']) < $this->config->noi) {
            $difference = sizeof($context['cards']) - $this->config->noi;

            // In case we get a negative flip
            if ($difference < 0) {
                $difference = abs($difference);
            }

            $workspaces           = $this->get_workspaces($instanceId);
            $context['has_cards'] = $workspaces['has_cards'];

            if (!empty($randomWorkspaceCards)) {
                $randomWorkspaceCards = array_rand($workspaces['cards'], $difference);
            }

            foreach ($randomWorkspaceCards as $random) {
                $context['cards'][] = $workspaces['cards'][$random];
            }
        }

        $context['classes'] = 'block-trv-tiles';
        $context['layout']  = 'horizontal';

        return $OUTPUT->render_from_template('block_totara_recently_viewed/main', $context);
    }

    /**
     * @param  int  $instanceId
     *
     * @return array
     * @throws coding_exception
     */
    private function get_recommendations(int $instanceId): array
    {
        global $CFG, $USER;

        $blockMode = block_mode_factory::get_block_mode(block_mode_factory::BLOCK_WORKSPACES);
        $count     = ($this->config->noi ?? $CFG->block_totara_recommendations_recctr) ?? 3;
        $items     = $blockMode->get_items($count, $USER->id);

        if (empty($items)) {
            return ['cards' => []];
        }

        return $this->generate_card_content($items, $instanceId, [
            'classes'   => 'block-trv-tiles',
            'layout'    => 'horizontal',
            'cards'     => [],
            'has_cards' => (sizeof($items) > 0) ?? false,
        ]);
    }

    /**
     * @param  int  $instanceId
     *
     * @return array
     * @throws coding_exception
     */
    private function get_workspaces(int $instanceId): array
    {
        global $USER;

        $workspaceQuery = query::from_parameters(['source' => 'ALL', 'sort' => 'RECENT',], $USER->id);

        $workspaceQuery->set_actor_id($USER->id);

        $workspaces = (loader::get_workspaces($workspaceQuery))->get_items()->all();

        return $this->generate_card_content($workspaces, $instanceId, [
            'classes'   => 'block-trv-tiles',
            'layout'    => 'horizontal',
            'cards'     => [],
            'has_cards' => (sizeof($workspaces) > 0) ?? false,
        ]);
    }

    /**
     * @param  string  $component
     * @param  card  $card
     * @param  object  $extraWorkspaceData
     * @param  int  $index
     *
     * @return array
     * @throws coding_exception
     */
    private function render_interaction_item(
        string $component,
        card $card,
        object $extraWorkspaceData,
        int $index
    ): array {
        global $PAGE;

        $classes      = $index === 0 ? 'block-trv-card-first' : '';
        $is_dashboard = $PAGE->pagelayout === 'dashboard';
        $url          = $card->get_url($is_dashboard);

        if ($card->is_library_card() && !$is_dashboard) {
            $url->param('source_url', $PAGE->url->out_as_local_url(true));
        }

        return [
            'is_' . $component => true,
            'classes'          => $classes,
            'item_id'          => $card->get_id(),
            'component'        => $component,
            'url'              => $url->out(false),
            'image'            => $extraWorkspaceData->image->out(false),
            'title'            => $extraWorkspaceData->title,
            'show_reactions'   => $this->config->ratings ?? settings::DEFAULT_SHOW_RATINGS,
            'extra'            => $card->get_extra_data(),
            'is_tile'          => true,
        ];
    }

    /**
     * @param  array  $items
     * @param  int  $instanceId
     * @param  array  $context
     *
     * @return array
     * @throws coding_exception
     */
    private function generate_card_content(array $items, int $instanceId, array $context): array
    {
        if (empty($items)) {
            return [];
        }

        $index = 0;

        foreach ($items as $item) {
            $item = workspace::from_id($item->id ?? $item->item_id);
            $card = card_resolver::create_card('container_workspace', $item->get_id());

            if (!$card) {
                continue;
            }

            $workspaceData = (object)[
                'title' => $item->get_name(),
                'image' => $item->get_image(),
            ];

            $content = $this->render_interaction_item('container_workspace', $card, $workspaceData, $index);

            if ($content) {
                $content['instance_id'] = $instanceId;
                $context['cards'][]     = $content;
            }

            $index++;
        }

        return $context;
    }
}