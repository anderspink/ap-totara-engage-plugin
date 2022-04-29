<template>
  <EngageSidePanel v-if="!$apollo.loading" class="tui-engageArticleSidePanel">
    <MiniProfileCard
      slot="author-profile"
      :display="user.card_display"
      :no-border="true"
      :no-padding="true"
    >
      <template v-slot:drop-down-items>
        <DropdownItem
          v-if="article.owned || article.updateable"
          @click="openModalFromAction = true"
        >
          {{ $str('delete', 'core') }}
        </DropdownItem>
        <DropdownItem v-if="!article.owned" @click="reportResource">
          {{ $str('reportresource', 'engage_article') }}
        </DropdownItem>
      </template>
    </MiniProfileCard>

    <template v-slot:modal>
      <ConfirmationModal
        :open="openModalFromAction"
        :loading="deleting"
        :title="$str('deletewarningtitle', 'engage_article')"
        :confirm-button-text="$str('delete', 'core')"
        @confirm="handleDelete"
        @cancel="openModalFromAction = false"
      >
        {{ $str('deletewarningmsg', 'engage_article') }}
      </ConfirmationModal>
    </template>

    <template v-slot:overview>
      <Loader :fullpage="true" :loading="submitting" />
      <p class="tui-engageArticleSidePanel__timeDescription">
        {{ article.timedescription }}
      </p>
      <AccessSetting
        v-if="article.owned || article.updateable"
        :item-id="resourceId"
        component="engage_anderspink"
        :access-value="article.resource.access"
        :topics="article.topics"
        :submitting="false"
        :open-access-modal="openModalFromButtonLabel"
        :selected-time-view="article.timeview"
        :enable-time-view="true"
        @access-update="updateAccess"
        @close-modal="openModalFromButtonLabel = false"
      />
      <AccessDisplay
        v-else
        :access-value="article.resource.access"
        :time-view="article.timeview"
        :topics="article.topics"
        :show-button="false"
      />

      <MediaSetting
        :owned="article.owned"
        :access-value="article.resource.access"
        :instance-id="resourceId"
        :share-button-aria-label="shareButtonLabel"
        :shared-by-count="article.sharedbycount"
        :like-button-aria-label="likeButtonLabel"
        :liked="article.reacted"
        :show-like-button="!isPrivateResource"
        component-name="engage_anderspink"
        @access-update="updateAccess"
        @access-modal="openModalFromButtonLabel = true"
        @update-like-status="updateLikeStatus"
      />

      <AnderspinkPlaylistBox
        :resource-id="resourceId"
        class="tui-engageArticleSidePanel__playlistBox"
      />
    </template>

    <template v-slot:comments>
      <SidePanelCommentBox
        component="engage_anderspink"
        area="comment"
        editor-variant="engage_article-comment"
        :instance-id="resourceId"
        :editor-context-id="article.resource.context_id"
      />
    </template>
  </EngageSidePanel>
</template>

<script>
import apolloClient from 'tui/apollo_client';
import Loader from 'tui/components/loading/Loader';
import SidePanelCommentBox from 'totara_comment/components/box/SidePanelCommentBox';
import AccessDisplay from 'totara_engage/components/sidepanel/access/AccessDisplay';
import AccessSetting from 'totara_engage/components/sidepanel/access/AccessSetting';
import EngageSidePanel from 'totara_engage/components/sidepanel/EngageSidePanel';
import ConfirmationModal from 'tui/components/modal/ConfirmationModal';
import MediaSetting from 'totara_engage/components/sidepanel/media/MediaSetting';
import MiniProfileCard from 'tui/components/profile/MiniProfileCard';
import DropdownItem from 'tui/components/dropdown/DropdownItem';
import AnderspinkPlaylistBox from "anderspink/components/sidepanel/content/AnderspinkPlaylistBox";
import { notify } from 'tui/notifications';
import { AccessManager } from 'totara_engage/index';

// GraphQL queries
import getArticle from 'engage_anderspink/graphql/get_article';
import engageAdvancedFeatures from 'totara_engage/graphql/advanced_features';
import createReview from 'totara_reportedcontent/graphql/create_review';
import deleteArticle from 'engage_anderspink/graphql/delete_article';
import updateArticle from 'engage_anderspink/graphql/update_article';

export default {
  components: {
    AccessDisplay,
    AccessSetting,
    AnderspinkPlaylistBox,
    EngageSidePanel,
    ConfirmationModal,
    Loader,
    MediaSetting,
    SidePanelCommentBox,
    MiniProfileCard,
    DropdownItem,
  },

  props: {
    resourceId: {
      type: [Number, String],
      required: true,
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
    },

    features: {
      query: engageAdvancedFeatures,
    },
  },

  data() {
    return {
      article: {},
      deleting: false,
      submitting: false,
      openModalFromButtonLabel: false,
      openModalFromAction: false,
      features: {},
    };
  },

  computed: {
    user() {
      if (!this.article.resource || !this.article.resource.user) {
        return {};
      }

      return this.article.resource.user;
    },

    shareButtonLabel() {
      if (this.article.owned) {
        return this.$str(
          'shareresource',
          'engage_article',
          this.article.resource.name
        );
      }

      return this.$str(
        'reshareresource',
        'engage_article',
        this.article.resource.name
      );
    },

    likeButtonLabel() {
      if (this.article.reacted) {
        return this.$str(
          'removelikearticle',
          'engage_article',
          this.article.resource.name
        );
      }

      return this.$str(
        'likearticle',
        'engage_article',
        this.article.resource.name
      );
    },

    featureRecommenders() {
      return this.features && this.features.recommenders;
    },

    isPrivateResource() {
      return AccessManager.isPrivate(this.article.resource.access);
    },
  },

  methods: {
    /**
     * Updates Access for this article
     *
     * @param {String} access The access level of the article
     * @param {Array} topics Topics that this article should be shared with
     * @param {Array} shares An array of group id's that this article is shared with
     */
    updateAccess({ access, topics, shares, timeView }) {
      this.submitting = true;
      this.$apollo
        .mutate({
          mutation: updateArticle,
          refetchAll: false,
          variables: {
            resourceid: this.resourceId,
            access: access,
            topics: topics.map(({ id }) => id),
            shares: shares,
            timeview: timeView,
          },

          update: (proxy, { data }) => {
            proxy.writeQuery({
              query: getArticle,
              variables: { id: this.resourceId },
              data,
            });
          },
        })
        .finally(() => {
          this.submitting = false;
        });
    },

    handleDelete() {
      this.deleting = true;
      this.$apollo
        .mutate({
          mutation: deleteArticle,
          variables: {
            id: this.resourceId,
          },
          refetchAll: false,
        })
        .then(({ data }) => {
          if (data.article) {
            this.$children.openModal = false;
            window.location.href = this.$url(
              '/totara/engage/your_resources.php'
            );
          }
        });
    },

    /**
     *
     * @param {Boolean} status
     */
    updateLikeStatus(status) {
      let { article } = apolloClient.readQuery({
        query: getArticle,
        variables: {
          id: this.resourceId,
        },
      });

      article = Object.assign({}, article);
      article.reacted = status;

      apolloClient.writeQuery({
        query: getArticle,
        variables: { id: this.resourceId },
        data: { article: article },
      });
    },

    /**
     * Report the attached resource
     * @returns {Promise<void>}
     */
    async reportResource() {
      if (this.submitting) {
        return;
      }
      this.submitting = true;
      try {
        let response = await this.$apollo
          .mutate({
            mutation: createReview,
            refetchAll: false,
            variables: {
              component: 'engage_article',
              area: '',
              item_id: this.resourceId,
              url: window.location.href,
            },
          })
          .then(response => response.data.review);

        if (response.success) {
          await notify({
            message: this.$str('reported', 'totara_reportedcontent'),
            type: 'success',
          });
        } else {
          await notify({
            message: this.$str('reported_failed', 'totara_reportedcontent'),
            type: 'error',
          });
        }
      } catch (e) {
        await notify({
          message: this.$str('error:reportresource', 'engage_article'),
          type: 'error',
        });
      } finally {
        this.submitting = false;
      }
    },
  },
};
</script>
<lang-strings>
  {
    "engage_article": [
      "deletewarningmsg",
      "deletewarningtitle",
      "reshareresource",
      "shareresource",
      "timelessthanfive",
      "timefivetoten",
      "timemorethanten",
      "likearticle",
      "removelikearticle",
      "reportresource",
      "error:reportresource"
    ],
    "core": [
      "delete"
    ],
    "totara_reportedcontent": [
      "reported",
      "reported_failed"
    ]
  }
</lang-strings>

<style lang="scss">
.tui-engageArticleSidePanel {
  &__timeDescription {
    @include tui-font-body-small();
  }

  &__playlistBox {
    margin-top: var(--gap-8);
  }
}
</style>
