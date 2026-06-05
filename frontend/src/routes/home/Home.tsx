import { Component } from 'react'
import { Link } from 'react-router-dom';
import './home.css'

export default class Home extends Component {
  render(){
    return (
      <>
      <div className="surface-0 text-center mt-4">
        <div className="mt-4">
          <div className="font-bold text-3xl">Welcome to the Tilecal Electronics Database </div>
          <div className="mt-4">Here you can see the current status of each Tilecal component. Use the navbar on top to naviagte through the pages or choose one of the options bellow.</div>
        </div>
        <div className="grid mt-4">
          <div className="col-12 md:col-6 mt-4 px-5">
              <span className="p-3 shadow-2 mb-3 inline-block" style={{ borderRadius: '10px' }}>
                  <i className="bi bi-pci-card text-4xl text-blue-500"></i>
              </span>
              <div className="text-900 text-xl mb-3 font-medium">Equipment</div>
              <div className="text-700 line-height-3"><Link to='equipment'>Index</Link></div>
          </div>
          <div className="col-12 md:col-6 mt-4 px-5">
              <span className="p-3 shadow-2 mb-3 inline-block" style={{ borderRadius: '10px' }}>
                  <i className="pi pi-file text-4xl text-blue-500"></i>
              </span>
              <div className="text-900 text-xl mb-3 font-medium">Files</div>
              <span className="text-700 line-height-3"><Link to='file'>Index</Link></span>
          </div>
        </div>
      </div>
      </>
    );
  }
}