<template>
  <Layout class="tui-workspacePage">
    <template v-slot:left="{ direction }">
      <SidePanel
        v-if="direction === 'horizontal'"
        :show-button-control="false"
        :sticky="false"
        :initially-open="true">
        <AndersPinkWorkspaceMenu/>
      </SidePanel>
    </template>

    <template v-slot:right="{ direction }">
      <div class="tui-workspacePage__mainContent">
        <NewWorkspaceBriefing slot="heading-side" @add-workspace="addWorkspacesBridge"/>
      </div>

      <div class="tui-workspacePage__mainContent">
        <WorkspaceBriefings
          ref="entriesTable"
        />
      </div>
    </template>

  </Layout>
</template>

<script>
import Layout from "tui/components/layouts/LayoutTwoColumn";
import SidePanel from "tui/components/sidepanel/SidePanel";
import AndersPinkWorkspaceMenu from "anderspink/components/sidepanel/AndersPinkWorkspaceMenu";
import WorkspaceBriefings from "anderspink/components/table/WorkspaceBriefings";
import NewWorkspaceBriefing from "anderspink/components/create/NewWorkspaceBriefing";

export default {
  components: {
    NewWorkspaceBriefing,
    WorkspaceBriefings,
    Layout,
    SidePanel,
    AndersPinkWorkspaceMenu
  },

  methods: {
    addWorkspacesBridge(workspace) {
      this.$refs.entriesTable.updateTableAfterAdd(workspace);
    }
  }
}
</script>