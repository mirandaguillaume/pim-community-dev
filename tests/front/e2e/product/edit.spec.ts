import {test, expect} from '@playwright/test';
import {login, goToProductsGrid, selectFirstProduct, saveProduct, reloadProduct, firstTextField} from '../fixtures/pim';

test('User can enrich the first product of the products grid', async ({page}) => {
  await login(page, 'admin', 'admin');
  await goToProductsGrid(page);
  await selectFirstProduct(page);

  const field = firstTextField(page);
  await field.clear();
  await field.fill('updated value');
  await saveProduct(page);

  await reloadProduct(page);
  await expect(firstTextField(page)).toHaveValue('updated value');
});
