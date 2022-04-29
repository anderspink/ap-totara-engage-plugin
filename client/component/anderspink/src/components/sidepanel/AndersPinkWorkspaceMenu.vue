<template>
  <SidePanelNav
    class="tui-workspaceMenu"
    :aria-label="$str('workspace_navigation_aria_label', 'container_anderspink')"
  >
    <SidePanelNavGroup
      :title="$str('nav_menu_title', 'container_anderspink')"
      class="tui-workspaceMenu__group"
    >
      <template>
        <SidePanelNavLinkItem
          v-for="(item, index) in menuItems"
          :id="index"
          :key="index"
          :text="$str(item.name, 'container_anderspink')"
          :url="$url(item.url)"
          :active="isActive(item.url, index)"
        />
      </template>
    </SidePanelNavGroup>
  </SidePanelNav>
</template>

<script>
import SidePanelNav from "tui/components/sidepanel/SidePanelNav";
import SidePanelNavGroup from "tui/components/sidepanel/SidePanelNavGroup";
import SidePanelNavLinkItem from "tui/components/sidepanel/SidePanelNavLinkItem";

export default {
  components: {
    SidePanelNavGroup,
    SidePanelNav,
    SidePanelNavLinkItem
  },

  data() {
    return {
      menuItems: [
        {
          name: 'anderspink_api_settings',
          url: '/container/type/anderspink/api_settings.php'
        },
        {
          name: 'anderspink_briefings',
          url: '/container/type/anderspink/briefings.php'
        }
      ]
    }
  },

  methods: {
    isActive (url, index) {
      if (window.location.pathname.indexOf(url) > -1) {
       return index;
      }

      return -1;
    }
  }
}
</script>

<lang-strings>
{
  "container_anderspink": [
    "workspace_navigation_aria_label",
    "anderspink_api_settings",
    "anderspink_briefings",
    "nav_menu_title"
  ]
}
</lang-strings>

<style lang="scss">
.tui-workspaceMenu {
  width: 100%;
  max-height: 100%;

  &__separator {
    width: 18%;
    margin-left: var(--gap-4);
    border: var(--border-width-thin) dashed var(--color-neutral-5);
  }
}
</style>