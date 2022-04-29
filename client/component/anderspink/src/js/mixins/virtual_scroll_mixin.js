export default {
  data() {
    return {
      isLoadMoreVisible: false,
    };
  },

  methods: {
    async onScrollToBottom() {
      if (this.isLoadMoreVisible) {
        return;
      }
      await this.loadMoreItems();
      this.isLoadMoreVisible = true;
    },

    async loadMore() {
      await this.loadMoreItems();
      this.isLoadMoreVisible = false;
    },
  },
};
