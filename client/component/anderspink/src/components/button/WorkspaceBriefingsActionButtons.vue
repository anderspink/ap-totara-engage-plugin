<template>
  <div>
    <div class="action-buttons">
      <LoadingButton
        class="tui-formBtn--prim tui-formBtn--small"
        :loading="submittingDelete"
        :text="$str('action_delete_btn', 'container_anderspink')"
        @click="deleteEntry"
      />
    </div>
  </div>
</template>

<script>
import LoadingButton from "totara_engage/components/buttons/LoadingButton";

// GraphQL
import deleteApi from 'container_anderspink/graphql/delete_workspace_briefing_bridge';
import {notify} from "tui/notifications";

export default {
  components: {
    LoadingButton,
  },

  props: {
    deleteId: {
      required: true,
      type: Number
    }
  },

  data() {
    return {
      submittingDelete: false,
    }
  },

  methods: {
    async deleteEntry() {
      if (this.submittingDelete) {
        return;
      }

      this.submittingDelete = true;

      try {
        const data = await this.$apollo.mutate({
          mutation: deleteApi,
          refetchAll: false,
          variables: {
            id: this.deleteId
          },
        });

        const emitData = {
          id: this.deleteId,
          result: data.data.bridge
        };

        this.$emit('delete-bridge', emitData);
      } catch (e) {
        await notify({
          message: this.$str('error:delete_api', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.submittingDelete = false;
      }
    },
  }
}
</script>

<lang-strings>
{
"container_anderspink": [
"action_edit_btn",
"action_delete_btn",
"error:delete_api"
]
}
</lang-strings>

<style lang="scss">
.action-buttons {
  button {
    width: 80px;
    margin-bottom: 5px;
    white-space: nowrap;
  }
}
</style>