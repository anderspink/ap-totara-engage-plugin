<template>
  <div class="tui-contributeWorkspace">
    <ModalPresenter :open="openModal" @request-close="openModal = false">
      <NewApiModal @add-api="addApi"/>
    </ModalPresenter>

    <ButtonIcon
      class="tui-contributeWorkspace__button"
      :styleclass="{ primary: true, text: true }"
      :aria-label="buttonIconAriaLabel"
      :disabled="disabled"
      @click.prevent="openModal = true"
    >
      <AddIcon/>
      <span> {{ buttonIconAriaLabel }} </span>
    </ButtonIcon>
  </div>
</template>

<script>
import ModalPresenter from "tui/components/modal/ModalPresenter";
import ButtonIcon from "tui/components/buttons/ButtonIcon";
import AddIcon from "tui/components/icons/Add";
import NewApiModal from "anderspink/components/modal/NewApi";

export default {
  components: {
    NewApiModal,
    ModalPresenter,
    ButtonIcon,
    AddIcon,
  },

  props: {
    disabled: Boolean,
    buttonIconAriaLabel: {
      type: String,
      default() {
        return this.$str('aria_label_add_api', 'container_anderspink')
      }
    }
  },

  data() {
    return {
      submitting: false,
      openModal: false,
    }
  },

  methods: {
    addApi(data) {
      this.openModal = false;
      this.$emit('add-api', data);
    }
  }
}
</script>

<lang-strings>
{
  "container_anderspink": [
    "aria_label_add_api"
  ]
}
</lang-strings>
