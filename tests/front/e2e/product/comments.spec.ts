import {test, expect} from '../fixtures/coverage-fixture';
import {login, createProductViaApi, goToProductBySearch, waitForLoadingMasks} from '../fixtures/pim';

/**
 * Replaces Behat: tests/legacy/features/pim/enrichment/product/pef/comments.feature:58
 *   "Successfully remove my own comments"
 *
 * Legacy Backbone widget (Context/Page/Product/Edit.php), not React — no stale-selector risk.
 *
 * Selectors traced from:
 * - "I visit the "Comments" column tab": Base.php::visitColumnTab() -> '.column-navigation-link'
 *   (same pattern as product/classify-product.spec.ts's "Categories" tab).
 * - Comments container: Product/Edit.php `'Comment threads' => '.comment-threads'`.
 * - "I add a new comment "<message>"": Edit.php::createComment() -> within '.comment-threads',
 *   'li.comment-create textarea', then press the "Add a new comment" button. The Post/Cancel
 *   buttons start hidden (templates/product/comments.html: '.AknButtonList--hide') and are only
 *   revealed by comments.js's `'keyup .comment-create textarea, ...': 'toggleButtons'` handler —
 *   a real keyboard event, which Locator.fill() does not dispatch (it sets the value directly).
 *   pressSequentially() is used instead so keyup actually fires.
 * - "I delete the "<message>" comment": Edit.php::deleteComment() -> the comment node's
 *   'span.remove-comment'.
 * - "I should see the text "Confirm deletion"" / "I confirm the removal": comments.js's
 *   removeComment() -> Dialog.confirmDelete() -> Dialog.confirm() (js/pim-dialog.js), which adds
 *   'modal--fullPage' to the dialog element. Scoped to 'div.modal--fullPage' rather than the
 *   broader 'div.modal, div[role="dialog"]' pattern used on pages without a WYSIWYG field
 *   (critical/category.spec.ts): the product edit form can carry a Summernote rich-text
 *   attribute, which pre-renders hidden '.note-image-dialog' / '.note-link-dialog' /
 *   '.note-help-dialog' elements that also carry the plain 'modal' class and strict-mode-violate
 *   a broader match (bit found live in product-model/remove-product-model.spec.ts, PR #388).
 *
 * Uses its own disposable product instead of the footwear catalog's "rangers", so the test is
 * self-contained.
 */

test.describe('Product comments', () => {
  test.beforeEach(async ({page}) => {
    await login(page, 'admin', 'admin');
  });

  test('can add and remove my own comment', async ({page}) => {
    const sku = `pw-comment-${Date.now()}`;
    const createResp = await createProductViaApi(page, sku);
    expect(createResp.ok(), `Create product ${sku} failed: ${createResp.status()}`).toBeTruthy();

    await goToProductBySearch(page, sku);

    // I visit the "Comments" column tab
    await page.locator('.column-navigation-link').filter({hasText: 'Comments'}).click();
    await waitForLoadingMasks(page);

    const commentThreads = page.locator('.comment-threads');
    await expect(commentThreads).toBeVisible({timeout: 15_000});
    await expect(commentThreads.getByText('No comment for now')).toBeVisible({timeout: 15_000});

    // I add a new comment "<message>"
    const message = `My comment ${Date.now()}`;
    const commentTextarea = commentThreads.locator('li.comment-create textarea');
    await commentTextarea.click();
    await commentTextarea.pressSequentially(message);
    await commentThreads.getByRole('button', {name: 'Add a new comment'}).click();

    await expect(commentThreads.getByText('No comment for now')).not.toBeVisible({timeout: 15_000});
    const commentNode = commentThreads.locator('li.comment-topic').filter({hasText: message});
    await expect(commentNode).toBeVisible({timeout: 15_000});

    // I delete the "<message>" comment
    await commentNode.locator('span.remove-comment').click();

    const confirmDialog = page.locator('div.modal--fullPage');
    await expect(confirmDialog).toBeVisible({timeout: 10_000});
    await expect(confirmDialog.getByText('Confirm deletion')).toBeVisible();
    await confirmDialog.locator('.ok').click();

    // Then I should not see the text "<message>"
    await expect(page.getByText(message)).not.toBeVisible({timeout: 15_000});
  });
});
