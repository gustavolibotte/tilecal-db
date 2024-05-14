import FileForm from '../../components/file_form/FileForm';
import './file_upload.css';

export default function FileUpload() {
    return (
      <div className="col-12">
        <h1>File Upload</h1>
        <p><b>*</b> Means the field is required</p>
        <FileForm/>
      </div>
    )
}
