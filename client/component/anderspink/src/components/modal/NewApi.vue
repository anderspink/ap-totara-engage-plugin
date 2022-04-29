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
      :title="$str('modal_title_new_api', 'container_anderspink')"
      :title-id="$id('modal_title_new_api')"
    >

      <ApiForm
        class="tui-workspaceModal__form"
        :submitting="submitting"
        @submit="addApi"
        @cancel="$emit('request-close')"
      />

    </ModalContent>
  </Modal>
</template>

<script>
import Modal from "tui/components/modal/Modal";
import ModalContent from "tui/components/modal/ModalContent";
import ApiForm from "anderspink/components/form/ApiForm";
import {notify} from 'tui/notifications';

// GraphQL queries
import addApi from 'container_anderspink/graphql/add_api';

export default {
  components: {
    ApiForm,
    ModalContent,
    Modal
  },

  data() {
    return {
      submitting: false
    }
  },

  methods: {
    async addApi({teamName, apiKey}) {
      if (this.submitting) {
        return;
      }

      this.submitting = true;

      try {
        const data = await this.$apollo.mutate({
          mutation: addApi,
          refetchAll: false,
          variables: {
            name: teamName,
            key: apiKey
          },
        });
        this.$emit('add-api', data.data.anderspink);
      } catch (e) {
        await notify({
          message: this.$str('error:add_api', 'container_anderspink'),
          type: 'error',
        });
      } finally {
        this.submitting = false;
      }
    }
  }
}
</script>

<lang-strings>
  {
    "container_anderspink": [
      "modal_title_new_api",
      "error:add_api"
    ]
  }
</lang-strings>

<style lang="scss">
.tui-workspaceModal {
  &__form {
    flex-grow: 1;
    height: 100%;
  }
}
</style>