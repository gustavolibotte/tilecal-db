import { Component } from 'react'
import { ColumnMeta, BaseTable } from '../base_table/BaseTable';
import './file_index.css'

export default class FileIndex extends Component {
  render(){
    const visibleColumns: ColumnMeta[] = [
      {field: 'description', header: 'Description'},
      {field: 'created_at', header: 'Created at'},
      {field: 'updated_at', header: 'Updated at'}
    ];

    return (
      <>
        <h1>File Index</h1>
        <BaseTable
        visibleColumns={visibleColumns}
        dataKey='name'
        dataKeyHeader='Name'
        fetchUrl='http://localhost:8080/api/file'/>
      </>
    );
  }
  
}
