import { Component } from 'react'
import { ColumnMeta, BaseTable } from '../base_table/BaseTable';
import { Button } from 'primereact/button';
import './file_index.css'

export default class FileIndex extends Component {
  render(){

    const getButton = (rowData: any) => {
      return <Button label={rowData.name}/>;
    }

    const columns: ColumnMeta[] = [
      {field: 'name', header: 'name', body: getButton},
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
        fetchUrl='http://localhost:8080/api/file'/>
      </>
    );
  }
  
}
