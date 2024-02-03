import FileTable from '../../components/file_table/FileTable';
import './file_index.css'

export default function FileIndex(){
  return (
    <div className="col-12">
      <h1>File Index</h1>
      <FileTable queryKey={['file', 'get', {}]}/>
    </div>
  );
}
