import React from 'react';
import {render, screen} from '@testing-library/react';
import '@testing-library/jest-dom';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {
  Table,
  Row,
  HeaderCell,
  Cell,
} from '../../../../../../../front/src/application/component/Dashboard/Widgets/Table';

const renderWith = (ui: React.ReactElement) =>
  render(
    <DependenciesProvider>
      <ThemeProvider theme={pimTheme}>{ui}</ThemeProvider>
    </DependenciesProvider>
  );

describe('Table', () => {
  it('renders a table with AknGrid class', () => {
    const {container} = renderWith(
      <Table>
        <Row>
          <Cell>content</Cell>
        </Row>
      </Table>
    );
    expect(container.querySelector('table')).toHaveClass('AknGrid');
    expect(container.querySelector('table')).toHaveClass('AknGrid--unclickable');
  });

  it('renders children inside a tbody with AknGrid-body class', () => {
    const {container} = renderWith(
      <Table>
        <Row>
          <Cell>content</Cell>
        </Row>
      </Table>
    );
    expect(screen.getByText('content')).toBeInTheDocument();
    const tbody = container.querySelector('tbody');
    expect(tbody).toBeInTheDocument();
    expect(tbody).toHaveClass('AknGrid-body');
  });
});

describe('Row', () => {
  it('has AknGrid-bodyRow class when isHeader is false (default)', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <Row>
            <td>row content</td>
          </Row>
        </tbody>
      </table>
    );
    expect(container.querySelector('tr')).toHaveClass('AknGrid-bodyRow');
  });

  it('has no AknGrid-bodyRow class when isHeader is true', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <Row isHeader>
            <td>row content</td>
          </Row>
        </tbody>
      </table>
    );
    expect(container.querySelector('tr')).not.toHaveClass('AknGrid-bodyRow');
  });

  it('renders children', () => {
    renderWith(
      <table>
        <tbody>
          <Row>
            <td>test cell</td>
          </Row>
        </tbody>
      </table>
    );
    expect(screen.getByText('test cell')).toBeInTheDocument();
  });
});

describe('Cell', () => {
  it('renders with default AknGrid-bodyCell class', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell>content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).toHaveClass('AknGrid-bodyCell');
  });

  it('applies action class when action=true', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell action>content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).toHaveClass('AknGrid-bodyCell--actions');
  });

  it('does not apply action class when action=false (default)', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell>content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).not.toHaveClass('AknGrid-bodyCell--actions');
  });

  it('applies highlight class when highlight=true', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell highlight>content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).toHaveClass('AknGrid-bodyCell--highlight');
  });

  it('does not apply highlight class when highlight=false (default)', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell>content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).not.toHaveClass('AknGrid-bodyCell--highlight');
  });

  it('applies both action and highlight classes when both are true', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell action highlight>
              content
            </Cell>
          </tr>
        </tbody>
      </table>
    );
    const cell = container.querySelector('td');
    expect(cell).toHaveClass('AknGrid-bodyCell--actions');
    expect(cell).toHaveClass('AknGrid-bodyCell--highlight');
  });

  it('renders children', () => {
    renderWith(
      <table>
        <tbody>
          <tr>
            <Cell>test content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(screen.getByText('test content')).toBeInTheDocument();
  });

  it('applies text alignment via style prop when align is set', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <Cell align="center">content</Cell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('td')).toHaveStyle({textAlign: 'center'});
  });
});

describe('HeaderCell', () => {
  it('renders children in a th with AknGrid-headerCell class', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <HeaderCell>Header</HeaderCell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('th')).toHaveClass('AknGrid-headerCell');
    expect(screen.getByText('Header')).toBeInTheDocument();
  });

  it('applies left alignment by default', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <HeaderCell>Header</HeaderCell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('th')).toHaveStyle({textAlign: 'left'});
  });

  it('applies custom alignment via align prop', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <HeaderCell align="right">Header</HeaderCell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('th')).toHaveStyle({textAlign: 'right'});
  });

  it('applies width via width prop', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <HeaderCell width={200}>Header</HeaderCell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('th')).toHaveStyle({width: '200px'});
  });

  it('applies width with string value', () => {
    const {container} = renderWith(
      <table>
        <tbody>
          <tr>
            <HeaderCell width="50%">Header</HeaderCell>
          </tr>
        </tbody>
      </table>
    );
    expect(container.querySelector('th')).toHaveStyle({width: '50%'});
  });
});
