import React, {useState} from 'react';
import {Table} from './Table';
import {Badge, Button, Image, IconButton} from '../../components';
import {DeleteIcon} from '../../icons';
import {rows, sortRows} from './shared-stories-data';
import {Scrollable} from '../../storybook/PreviewGallery';

export default {
  title: 'Components/Table',
  component: Table,

  subcomponents: {
    Table: Table,
    'Table.Header': Table.Header,
    'Table.HeaderCell': Table.HeaderCell,
    'Table.Body': Table.Body,
    'Table.Row': Table.Row,
    'Table.Cell': Table.Cell,
    'Table.ActionCell': Table.ActionCell,
  },

  argTypes: {
    displayImage: {
      control: {
        type: 'boolean',
      },

      description: 'Show or hide the "Image" column',
    },

    displayRowTitle: {
      control: {
        type: 'boolean',
      },

      description: 'Define the "Name" column as row title',
    },

    isSelectable: {
      table: {
        disable: true,
      },
    },

    displayCheckbox: {
      table: {
        disable: true,
      },
    },

    children: {
      table: {
        disable: true,
      },
    },
  },

  args: {
    displayRowTitle: false,
    displayImage: true,
  },
};

export const Standard = {
  render: args => (
    <Table>
      <Table.Header>
        {args.displayImage && <Table.HeaderCell>Image</Table.HeaderCell>}
        <Table.HeaderCell>Name</Table.HeaderCell>
        <Table.HeaderCell>Family</Table.HeaderCell>
        <Table.HeaderCell>Order</Table.HeaderCell>
        <Table.HeaderCell>Genus</Table.HeaderCell>
        <Table.HeaderCell>Conservation status</Table.HeaderCell>
        <Table.HeaderCell>Actions</Table.HeaderCell>
      </Table.Header>
      <Table.Body>
        <Table.Row>
          {args.displayImage && (
            <Table.Cell>
              <Image src="https://picsum.photos/seed/akeneo/200/140" alt="The alt" />
            </Table.Cell>
          )}
          <Table.Cell rowTitle={args.displayRowTitle}>Giant panda</Table.Cell>
          <Table.Cell>Ursidae</Table.Cell>
          <Table.Cell>Carnivora</Table.Cell>
          <Table.Cell>Ursus</Table.Cell>
          <Table.Cell>
            <Badge level="warning">vu</Badge>
          </Table.Cell>
          <Table.ActionCell>
            <Button level="primary" ghost>
              Button
            </Button>
          </Table.ActionCell>
        </Table.Row>
      </Table.Body>
    </Table>
  ),

  name: 'Standard',
};

export const SortableHeader = {
  render: args => {
    const [sortedColumn, setSortedColumn] = useState({
      columnName: null,
      sortDirection: null,
    });

    const computeDirection = columnName => {
      if (columnName !== sortedColumn.columnName) {
        return 'none';
      }

      return sortedColumn.sortDirection;
    };

    const handleDirectionChange = columnName => sortDirection => {
      setSortedColumn({
        columnName: columnName,
        sortDirection: sortDirection,
      });
    };

    const sortedRows = sortRows(rows, sortedColumn.columnName, sortedColumn.sortDirection);

    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>Image</Table.HeaderCell>
          <Table.HeaderCell
            isSortable={true}
            onDirectionChange={handleDirectionChange('name')}
            sortDirection={computeDirection('name')}
          >
            Name
          </Table.HeaderCell>
          <Table.HeaderCell
            isSortable={true}
            onDirectionChange={handleDirectionChange('family')}
            sortDirection={computeDirection('family')}
          >
            Family
          </Table.HeaderCell>
          <Table.HeaderCell>Order</Table.HeaderCell>
          <Table.HeaderCell>Genus</Table.HeaderCell>
          <Table.HeaderCell>Conservation status</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {sortedRows.map(row => (
            <Table.Row key={row.name}>
              <Table.Cell>
                <Image src={row.image} alt="The alt" />
              </Table.Cell>
              <Table.Cell>{row.name}</Table.Cell>
              <Table.Cell>{row.family}</Table.Cell>
              <Table.Cell>{row.order}</Table.Cell>
              <Table.Cell>{row.genus}</Table.Cell>
              <Table.Cell>
                <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" onClick={() => {}} ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    );
  },

  name: 'Sortable header',
};

export const DraggableRows = {
  render: () => {
    const manyRows = [...rows, ...rows, ...rows, ...rows, ...rows, ...rows];
    const [orderedRows, setOrderedRow] = useState(manyRows);

    return (
      <Scrollable height={500}>
        <Table
          isDragAndDroppable={true}
          onReorder={newIndices => {
            setOrderedRow(rows => newIndices.map(index => rows[index]));
          }}
        >
          <Table.Header>
            <Table.HeaderCell>Image</Table.HeaderCell>
            <Table.HeaderCell>Name</Table.HeaderCell>
            <Table.HeaderCell>Family</Table.HeaderCell>
            <Table.HeaderCell>Order</Table.HeaderCell>
            <Table.HeaderCell>Genus</Table.HeaderCell>
            <Table.HeaderCell>Conservation status</Table.HeaderCell>
            <Table.HeaderCell>Actions</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {orderedRows.map((row, index) => (
              <Table.Row key={`${row.id}${index}`}>
                <Table.Cell>
                  <Image src={row.image} alt="The alt" />
                </Table.Cell>
                <Table.Cell>
                  {row.name} {index}
                </Table.Cell>
                <Table.Cell>{row.family}</Table.Cell>
                <Table.Cell>{row.order}</Table.Cell>
                <Table.Cell>{row.genus}</Table.Cell>
                <Table.Cell>
                  <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
                </Table.Cell>
                <Table.ActionCell>
                  <Button level="primary" onClick={() => {}} ghost>
                    Button
                  </Button>
                </Table.ActionCell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      </Scrollable>
    );
  },

  name: 'Draggable rows',
};

export const SelectableLines = {
  render: args => {
    const [selectedLines, setSelectedLines] = useState([]);

    const handleToggleSelected = lineId => {
      if (selectedLines.indexOf(lineId) === -1) {
        setSelectedLines([...selectedLines, lineId]);
      } else {
        setSelectedLines(selectedLines.filter(currentValue => currentValue !== lineId));
      }
    };

    return (
      <Table isSelectable={true} displayCheckbox={selectedLines.length > 0}>
        <Table.Header>
          <Table.HeaderCell>Image</Table.HeaderCell>
          <Table.HeaderCell>Name</Table.HeaderCell>
          <Table.HeaderCell>Family</Table.HeaderCell>
          <Table.HeaderCell>Order</Table.HeaderCell>
          <Table.HeaderCell>Genus</Table.HeaderCell>
          <Table.HeaderCell>Conservation status</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {rows.map(row => (
            <Table.Row
              key={row.id}
              onSelectToggle={() => handleToggleSelected(row.id)}
              isSelected={selectedLines.indexOf(row.id) !== -1}
            >
              <Table.Cell>
                <Image src={row.image} alt="The alt" />
              </Table.Cell>
              <Table.Cell>{row.name}</Table.Cell>
              <Table.Cell>{row.family}</Table.Cell>
              <Table.Cell>{row.order}</Table.Cell>
              <Table.Cell>{row.genus}</Table.Cell>
              <Table.Cell>
                <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    );
  },

  name: 'Selectable lines',
};

export const ClickableLines = {
  render: args => {
    const [data, setData] = useState(
      rows.map(row => {
        return {
          ...row,
          count: 0,
        };
      })
    );

    const handleClick = rowId => {
      setData(data => {
        return data.map(row => {
          return row.id === rowId
            ? {
                ...row,
                count: row.count + 1,
              }
            : row;
        });
      });
    };

    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>Image</Table.HeaderCell>
          <Table.HeaderCell>Name</Table.HeaderCell>
          <Table.HeaderCell>Family</Table.HeaderCell>
          <Table.HeaderCell>Order</Table.HeaderCell>
          <Table.HeaderCell>Genus</Table.HeaderCell>
          <Table.HeaderCell>Conservation status</Table.HeaderCell>
          <Table.HeaderCell>Click count</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {data.map(row => (
            <Table.Row key={row.id} onClick={() => handleClick(row.id)}>
              <Table.Cell>
                <Image src={row.image} alt="The alt" />
              </Table.Cell>
              <Table.Cell>{row.name}</Table.Cell>
              <Table.Cell>{row.family}</Table.Cell>
              <Table.Cell>{row.order}</Table.Cell>
              <Table.Cell>{row.genus}</Table.Cell>
              <Table.Cell>
                <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
              </Table.Cell>
              <Table.Cell>{row.count}</Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    );
  },

  name: 'Clickable lines',
};

export const TableWithActions = {
  render: args => {
    return (
      <Table>
        <Table.Header>
          <Table.HeaderCell>Name</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          <Table.Row>
            <Table.Cell>A value without actions</Table.Cell>
            <Table.ActionCell />
          </Table.Row>
          <Table.Row>
            <Table.Cell>A value with a button action</Table.Cell>
            <Table.ActionCell>
              <Button level="primary" ghost>
                A button action
              </Button>
            </Table.ActionCell>
          </Table.Row>
          <Table.Row>
            <Table.Cell>A value with 2 buttons action</Table.Cell>
            <Table.ActionCell>
              <Button level="primary" ghost>
                A button action
              </Button>
              <Button level="secondary" ghost>
                A button action
              </Button>
            </Table.ActionCell>
          </Table.Row>
          <Table.Row>
            <Table.Cell>A value with an icon button action</Table.Cell>
            <Table.ActionCell>
              <IconButton level="primary" ghost icon={<DeleteIcon />}>
                A button action
              </IconButton>
            </Table.ActionCell>
          </Table.Row>
        </Table.Body>
      </Table>
    );
  },

  name: 'Table with actions',
};

export const ComplexTable = {
  render: args => {
    const [selectedLines, setSelectedLines] = useState([2, 3]);

    const handleToggleSelected = lineId => {
      if (selectedLines.indexOf(lineId) === -1) {
        setSelectedLines([...selectedLines, lineId]);
      } else {
        setSelectedLines(selectedLines.filter(currentValue => currentValue !== lineId));
      }
    };

    const [data, setData] = useState(
      rows.map(row => {
        return {
          ...row,
          count: 0,
        };
      })
    );

    const handleClick = rowId => {
      setData(data =>
        data.map(row =>
          row.id === rowId
            ? {
                ...row,
                count: row.count + 1,
              }
            : row
        )
      );
    };

    return (
      <Table isSelectable={true} displayCheckbox={selectedLines.length > 0}>
        <Table.Header>
          <Table.HeaderCell>Image</Table.HeaderCell>
          <Table.HeaderCell>Name</Table.HeaderCell>
          <Table.HeaderCell>Family</Table.HeaderCell>
          <Table.HeaderCell>Order</Table.HeaderCell>
          <Table.HeaderCell>Genus</Table.HeaderCell>
          <Table.HeaderCell>Conservation status</Table.HeaderCell>
          <Table.HeaderCell>Click count</Table.HeaderCell>
          <Table.HeaderCell>Actions</Table.HeaderCell>
        </Table.Header>
        <Table.Body>
          {data.map(row => (
            <Table.Row
              key={row.id}
              onClick={() => handleClick(row.id)}
              onSelectToggle={() => handleToggleSelected(row.id)}
              isSelected={selectedLines.indexOf(row.id) !== -1 ? (row.id == 3 ? 'mixed' : true) : false}
            >
              <Table.Cell>
                <Image src={row.image} alt="The alt" />
              </Table.Cell>
              <Table.Cell>{row.name}</Table.Cell>
              <Table.Cell>{row.family}</Table.Cell>
              <Table.Cell>{row.order}</Table.Cell>
              <Table.Cell>{row.genus}</Table.Cell>
              <Table.Cell>
                <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
              </Table.Cell>
              <Table.Cell>{row.count}</Table.Cell>
              <Table.ActionCell>
                <Button level="primary" onClick={() => {}} ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
          ))}
        </Table.Body>
      </Table>
    );
  },

  name: 'Complex table',
};

export const StickyHeader = {
  render: args => {
    const manyRows = [...rows, ...rows, ...rows, ...rows, ...rows, ...rows];

    return (
      <Scrollable height={250}>
        <Table>
          <Table.Header sticky={0}>
            {args.displayImage && <Table.HeaderCell>Image</Table.HeaderCell>}
            <Table.HeaderCell>Name</Table.HeaderCell>
            <Table.HeaderCell>Family</Table.HeaderCell>
            <Table.HeaderCell>Order</Table.HeaderCell>
            <Table.HeaderCell>Genus</Table.HeaderCell>
            <Table.HeaderCell>Conservation status</Table.HeaderCell>
            <Table.HeaderCell>Actions</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            {manyRows.map((row, index) => (
              <Table.Row key={`${row.id}${index}`}>
                <Table.Cell>
                  <Image src={row.image} alt="The alt" />
                </Table.Cell>
                <Table.Cell>
                  {row.name} {index}
                </Table.Cell>
                <Table.Cell>{row.family}</Table.Cell>
                <Table.Cell>{row.order}</Table.Cell>
                <Table.Cell>{row.genus}</Table.Cell>
                <Table.Cell>
                  <Badge level={row.conservation_status_level}>{row.conservation_status}</Badge>
                </Table.Cell>
                <Table.ActionCell>
                  <Button level="primary" ghost>
                    Button
                  </Button>
                </Table.ActionCell>
              </Table.Row>
            ))}
          </Table.Body>
        </Table>
      </Scrollable>
    );
  },

  name: 'Sticky header',
};

export const WarningAndLockedRows = {
  render: args => {
    return (
      <Scrollable height={250}>
        <Table hasWarningRows={true} hasLockedRows={true}>
          <Table.Header sticky={0}>
            <Table.HeaderCell>Name</Table.HeaderCell>
            <Table.HeaderCell>Family</Table.HeaderCell>
            <Table.HeaderCell>Order</Table.HeaderCell>
            <Table.HeaderCell>Genus</Table.HeaderCell>
            <Table.HeaderCell>Conservation status</Table.HeaderCell>
            <Table.HeaderCell>Actions</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            <Table.Row level="tertiary">
              <Table.Cell rowTitle={args.displayRowTitle}>Giant panda</Table.Cell>
              <Table.Cell>Ursidae</Table.Cell>
              <Table.Cell>Carnivora</Table.Cell>
              <Table.Cell>Ursus</Table.Cell>
              <Table.Cell>
                <Badge level="warning">vu</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
            <Table.Row level="warning">
              <Table.Cell rowTitle={args.displayRowTitle}>Giant panda</Table.Cell>
              <Table.Cell>Ursidae</Table.Cell>
              <Table.Cell>Carnivora</Table.Cell>
              <Table.Cell>Ursus</Table.Cell>
              <Table.Cell>
                <Badge level="warning">vu</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
            <Table.Row>
              <Table.Cell rowTitle={args.displayRowTitle}>Red panda</Table.Cell>
              <Table.Cell>Ailuridae</Table.Cell>
              <Table.Cell>Carnivora</Table.Cell>
              <Table.Cell>Ailurus</Table.Cell>
              <Table.Cell>
                <Badge level="warning">vu</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  Button
                </Button>
              </Table.ActionCell>
            </Table.Row>
          </Table.Body>
        </Table>
      </Scrollable>
    );
  },

  name: 'Warning and locked rows',
};

export const MultipleActions = {
  render: args => {
    return (
      <Scrollable height={250}>
        <Table hasWarningRows={true}>
          <Table.Header sticky={0}>
            <Table.HeaderCell>Name</Table.HeaderCell>
            <Table.HeaderCell>Family</Table.HeaderCell>
            <Table.HeaderCell>Order</Table.HeaderCell>
            <Table.HeaderCell>Genus</Table.HeaderCell>
            <Table.HeaderCell>Conservation status</Table.HeaderCell>
            <Table.HeaderCell>Actions</Table.HeaderCell>
          </Table.Header>
          <Table.Body>
            <Table.Row level="warning">
              <Table.Cell rowTitle={args.displayRowTitle}>Giant panda</Table.Cell>
              <Table.Cell>Ursidae</Table.Cell>
              <Table.Cell>Carnivora</Table.Cell>
              <Table.Cell>Ursus</Table.Cell>
              <Table.Cell>
                <Badge level="warning">vu</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  View
                </Button>
                <Button level="danger" ghost>
                  Delete
                </Button>
              </Table.ActionCell>
            </Table.Row>
            <Table.Row>
              <Table.Cell rowTitle={args.displayRowTitle}>Red panda</Table.Cell>
              <Table.Cell>Ailuridae</Table.Cell>
              <Table.Cell>Carnivora</Table.Cell>
              <Table.Cell>Ailurus</Table.Cell>
              <Table.Cell>
                <Badge level="warning">vu</Badge>
              </Table.Cell>
              <Table.ActionCell>
                <Button level="primary" ghost>
                  View
                </Button>
                <Button level="danger" ghost>
                  Delete
                </Button>
              </Table.ActionCell>
            </Table.Row>
          </Table.Body>
        </Table>
      </Scrollable>
    );
  },

  name: 'Multiple actions',
};
