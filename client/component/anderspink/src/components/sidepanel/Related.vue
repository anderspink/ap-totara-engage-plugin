<template>
  <div class="tui-engageArticleRelated">
    <article
      v-for="{
        bookmarked,
        instanceid,
        image,
        name,
        reactions,
        timeview,
        url,
      } in articles"
      :key="instanceid"
    >
      <RelatedCard
        :resource-id="instanceid"
        :bookmarked="bookmarked"
        :image="image"
        :name="name"
        :reactions="reactions"
        :timeview="timeview"
        :url="url"
        @update="update"
      />
    </article>
  </div>
</template>

<script>
import RelatedCard from "anderspink/components/card/RelatedCard";

import updateBookmark from 'totara_engage/graphql/update_bookmark';

export default {
  components: {
    RelatedCard,
  },
  props: {
    resourceId: {
      type: [Number, String],
      required: true,
    },
  },

  data() {
    return {
      articles: [],
    };
  },

  methods: {
    update(resourceId, bookmarked) {
      this.$apollo.mutate({
        mutation: updateBookmark,
        refetchAll: false,
        variables: {
          itemid: resourceId,
          component: 'engage_article',
          bookmarked,
        },
      });
    },
  },
};
</script>

<style lang="scss">
.tui-engageArticleRelated {
  & > * + * {
    margin-top: var(--gap-2);
  }
}
</style>
