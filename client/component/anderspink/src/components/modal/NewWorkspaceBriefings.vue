<template>
  <Modal
    class="tui-workspaceModal"
    size="large"
    :dismissable="{
      esc: true,
      backdropClick: false,
      overlayClose: false,
    }"
  >
    <ModalContent
      :close-button="false"
      :title="$str('modal_title_new_workspace_briefing', 'container_anderspink')"
      :title-id="$id('modal_title_new_workspace_briefing')"
    >
      <WorkspaceBriefingForm
        class="tui-workspaceModal__form"
        :submitting="submitting"
        @submit="newWorkspaceBriefing"
        @cancel="$emit('request-close')"
      />
    </ModalContent>
  </Modal>
</template>

<script>
import Modal from "tui/components/modal/Modal";
import ModalContent from "tui/components/modal/ModalContent";
import WorkspaceBriefingForm from 'anderspink/components/form/WorkspaceBriefingForm';
import {notify} from 'tui/notifications';

// GraphQL queries
import addWorkspaceBriefing from 'container_anderspink/graphql/add_workspace_briefing_bridge';

export default {
  components: {
    ModalContent,
    Modal,
    WorkspaceBriefingForm
  },

  data() {
    return {
      submitting: false
    }
  },

  methods: {
    async newWorkspaceBriefing({workspace, briefing, team}) {
      if (this.submitting) {
        return;
      }

      this.submitting = true;

      try {
        const [bridgeType, bridgeId] = briefing.split('_');

        const data = await this.$apollo.mutate({
          mutation: addWorkspaceBriefing,
          refetchAll: false,
          variables: {
            workspaceid: workspace,
            teamid: team,
            bridgetype: bridgeType,
            bridgeid: bridgeId,
          }
        });

        this.$emit('add-workspace', data.data.anderspink_workspace);
        await notify({
          message: this.$str('success:bridge_user_info', 'container_anderspink'),
          type: 'success'
        });
      } catch (e) {
        await notify({
          message: this.$str('error:add_workspace_briefing', 'container_anderspink'),
          type: 'error'
        });
      } finally {
        this.submitting = false
      }
    }
  }
}
</script>

<lang-strings>
  {
    "container_anderspink": [
      "error:add_workspace_briefing",
      "modal_title_new_workspace_briefing",
      "success:bridge_user_info"
    ]
  }
</lang-strings>