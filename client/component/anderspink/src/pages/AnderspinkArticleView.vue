<template>
  <Layout class="tui-engageArticleView">
    <template v-if="backButton || navigationButtons" v-slot:header>
      <ResourceNavigationBar
        :back-button="backButton"
        :navigation-buttons="navigationButtons"
        class="tui-engageArticleView__backButton"
      />
    </template>
    <template v-slot:column>
      <Loader :loading="$apollo.loading" :fullpage="true" />
      <div v-if="!$apollo.loading" class="tui-engageArticleView__layout">
        <AnderspinkTitle
          :title="articleName"
          :resource-id="resourceId"
          :owned="article.owned"
          :bookmarked="bookmarked"
          :update-able="article.updateable"
          @bookmark="updateBookmark"
        />
        <AnderspinkContent
          :title="articleName"
          :update-able="article.updateable"
          :content="article.content"
          :resource-id="resourceId"
        />
      </div>
    </template>
    <template v-slot:sidepanel>
      <AnderspinkSidePanel :resource-id="resourceId" />
    </template>
  </Layout>
</template>

<script>
import Layout from 'totara_engage/components/page/LayoutOneColumnContentWithSidePanel';
import Loader from 'tui/components/loading/Loader';

import AnderspinkSidePanel from "anderspink/components/sidepanel/AnderspinkSidePanel";
import AnderspinkContent from "anderspink/components/content/AnderspinkContent";
import AnderspinkTitle from "anderspink/components/content/AnderspinkTitle";
import ResourceNavigationBar from 'totara_engage/components/header/ResourceNavigationBar';

// GraphQL
import getArticle from 'engage_anderspink/graphql/get_article';
import updateBookmark from 'totara_engage/graphql/update_bookmark';

export default {
  components: {
    AnderspinkTitle,
    AnderspinkContent,
    AnderspinkSidePanel,
    Layout,
    Loader,
    ResourceNavigationBar,
  },

  props: {
    resourceId: {
      type: Number,
      required: true,
    },

    backButton: {
      type: Object,
      required: false,
    },

    navigationButtons: {
      type: Object,
      required: false,
    },
  },

  data() {
    return {
      article: {},
      bookmarked: false,
    };
  },

  computed: {
    articleName() {
      if (!this.article.resource || !this.article.resource.name) {
        return '';
      }

      return this.article.resource.name;
    },
  },

  apollo: {
    article: {
      query: getArticle,
      variables() {
        return {
          id: this.resourceId,
        };
      },
      result({ data: { article } }) {
        this.bookmarked = article.bookmarked;
      },
    },
  },

  methods: {
    updateBookmark() {
      this.bookmarked = !this.bookmarked;
      this.$apollo.mutate({
        mutation: updateBookmark,
        refetchAll: false,
        variables: {
          itemid: this.resourceId,
          component: 'engage_article',
          bookmarked: this.bookmarked,
        },
        update: proxy => {
          let { article } = proxy.readQuery({
            query: getArticle,
            variables: {
              id: this.resourceId,
            },
          });

          article = Object.assign({}, article);
          article.bookmarked = this.bookmarked;

          proxy.writeQuery({
            query: getArticle,
            variables: { id: this.resourceId },
            data: { article: article },
          });
        },
      });
    },
  },
};
</script>

<lang-strings>
{
"engage_article": [
"entercontent",
"entertitle"
]
}
</lang-strings>

<style lang="scss">
:root {
  --engageArticle-min-height: 78vh;
}

.tui-engageArticleView {
  .tui-grid-item {
    min-height: var(--engageArticle-min-height);
  }
  &__backButton {
    margin-bottom: var(--gap-12);
    padding: var(--gap-4) var(--gap-8);
  }
}
</style>
