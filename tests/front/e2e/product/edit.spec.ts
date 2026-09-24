import {test, expect} from '../fixtures/coverage-fixture';
import {
  login,
  goToProductsGrid,
  selectFirstProduct,
  saveProduct,
  reloadProduct,
  firstTextField,
  ensureProductExists,
} from '../fixtures/pim';

test('User can enrich the first product of the products grid', async ({page}) => {
  await login(page, 'admin', 'admin');
  const sku = await ensureProductExists(page);
  expect(
    sku,
    'ensureProductExists returned null: no family to attach a product to, or POST /enrich/product/rest refused it'
  ).toBeTruthy();
  await goToProductsGrid(page);
  await selectFirstProduct(page);

  const field = firstTextField(page);
  await field.clear();
  await field.fill('updated value');
  await saveProduct(page);

  await reloadProduct(page);
  await expect(firstTextField(page)).toHaveValue('updated value');
});
