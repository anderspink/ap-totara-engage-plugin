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
        <NewApiWorkspace slot="heading-side" @add-api="addApi"/>
      </div>

      <div class="tui-workspacePage__mainContent">
        <ApiEntriesTable
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
import ApiEntriesTable from "anderspink/components/table/ApiEntries";
import NewApiWorkspace from "anderspink/components/create/NewApiWorkspace";

export default {
  components: {
    NewApiWorkspace,
    ApiEntriesTable,
    Layout,
    SidePanel,
    AndersPinkWorkspaceMenu
  },

  methods: {
    addApi(anderspink_api) {
      this.$refs.entriesTable.updateTableAfterAdd(anderspink_api)
    }
  }
}
</script>