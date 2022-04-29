<template>
  <div class="tui-contributeWorkspace">
    <ModalPresenter :open="openModal" @request-close="openModal = false">
      <NewWorkspaceBriefings @add-workspace="addWorkspace"/>
    </ModalPresenter>

    <div class="display-flex">
      <div class="col">
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
      <div class="col">
        <ContributeWorkspace
          class="tui-contributeWorkspace__button"
        />
      </div>
    </div>
  </div>
</template>

<script>
import ModalPresenter from "tui/components/modal/ModalPresenter";
import NewWorkspaceBriefings from "anderspink/components/modal/NewWorkspaceBriefings";
import ButtonIcon from "tui/components/buttons/ButtonIcon";
import AddIcon from "tui/components/icons/Add";
import ContributeWorkspace from "anderspink/components/create/ContributeWorkspace";

export default {
  components: {
    ContributeWorkspace,
    ButtonIcon,
    NewWorkspaceBriefings,
    ModalPresenter,
    AddIcon
  },

  props: {
    disabled: Boolean,
    buttonIconAriaLabel: {
      type: String,
      default() {
        return this.$str('add_to_briefing_to_workspace_btn', 'container_anderspink')
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
    addWorkspace(data) {
      this.openModal = false;
      this.$emit('add-workspace', data);
    }
  }
}
</script>
<style lang="scss">
.display-flex {
  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  justify-content: flex-start;

  .col {
    margin-right: 10px;
  }
}
</style>
<lang-strings>
{
"container_anderspink": [
"add_to_briefing_to_workspace_btn"
]
}
</lang-strings>