import { Component } from 'react'
import { ColumnMeta, BaseTable } from '../../components/base_table/BaseTable';
import { Button } from 'primereact/button';
import { ENDPOINT } from '../../utils/DbClient';
import './file_index.css'

export default class FileIndex extends Component {
  render(){

    const getButton = (rowData: any) => {
      return <Button label={rowData.name}/>;
    }

    const columns: ColumnMeta[] = [
      {field: 'name', header: 'Name', body: getButton},
      {field: 'description', header: 'Description'},
      {field: 'created_at', header: 'Created at'},
      {field: 'updated_at', header: 'Updated at'}
    ];

    return (
      <>
        <h1>File Index</h1>
        <BaseTable
        columns={columns}
        expandField=''
        dataKey='name'
        dataKeyHeader='Name'
        fileName='file_index'
        endpoint={ENDPOINT['file']}
        queryKey={['file', 'get', {}]}/>
      </>
    );
  }
  
}
